<?php

use FireflyIII\Exception\FireflyException;
use Watson\Validating\ValidatingTrait;

class LimitRepetition extends Eloquent
{
    use ValidatingTrait;
    public static $rules
        = [
            'limit_id'  => 'required|exists:limits,id',
            'startdate' => 'required|date',
            'enddate'   => 'required|date',
            'amount'    => 'numeric|required|min:0.01',
        ];

    /**
     * @return array
     */
    public function getDates()
    {
        return ['created_at', 'updated_at', 'startdate', 'enddate'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function limit()
    {
        return $this->belongsTo('Limit');
    }

    /**
     * TODO see if this scope is still used.
     *
     * How much money is left in this?
     */
    public function leftInRepetition()
    {
        return floatval($this->amount - $this->spentInRepetition());

    }

    /**
     * TODO remove this method in favour of something in the FireflyIII libraries.
     *
     * @return float
     */
    public function spentInRepetition()
    {
        $sum = \DB::table('transactions')->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')->leftJoin(
            'component_transaction_journal', 'component_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id'
        )->leftJoin('components', 'components.id', '=', 'component_transaction_journal.component_id')->leftJoin(
            'limits', 'limits.component_id', '=', 'components.id'
        )->leftJoin('limit_repetitions', 'limit_repetitions.limit_id', '=', 'limits.id')->where(
            'transaction_journals.date', '>=', $this->startdate->format('Y-m-d')
        )->where('transaction_journals.date', '<=', $this->enddate->format('Y-m-d'))->where('transactions.amount', '>', 0)->where(
            'limit_repetitions.id', '=', $this->id
        )->sum('transactions.amount');

        return floatval($sum);
    }

    /**
     * TODO remove this method in favour of something in the FireflyIII libraries.
     *
     * Returns a string used to sort this particular repetition
     * based on the date and period it falls into. Ie. the limit
     * repeats monthly and the start date is 12 dec 2012, this will
     * return 2012-12.
     */
    public function periodOrder()
    {
        if (is_null($this->repeat_freq)) {
            $this->repeat_freq = $this->limit->repeat_freq;
        }
        switch ($this->repeat_freq) {
            default:
                throw new FireflyException('No date formats for frequency "' . $this->repeat_freq . '"!');
                break;
            case 'daily':
                return $this->startdate->format('Ymd') . '-5';
                break;
            case 'weekly':
                return $this->startdate->format('Ymd') . '-4';
                break;
            case 'monthly':
                return $this->startdate->format('Ymd') . '-3';
                break;
            case 'quarterly':
                return $this->startdate->format('Ymd') . '-2';
                break;
            case 'half-year':
                return $this->startdate->format('Ymd') . '-1';
                break;
            case 'yearly':
                return $this->startdate->format('Ymd') . '-0';
                break;
        }
    }

} 