@extends('layouts.default')
@section('content')
{{ Breadcrumbs::renderIfExists(Route::getCurrentRoute()->getName()) }}
<div class="row">
    <div class="col-lg-12 col-sm-12 col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa {{$mainTitleIcon}}"></i> {{{$title}}}
            <!-- TODO add menu -->
        </div>
        <div class="panel-body">
            @include('list.recurring')
        </div>
        </div>
    </div>
</div>
@stop
@section('scripts')

@stop