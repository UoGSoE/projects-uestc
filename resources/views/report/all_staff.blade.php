@extends('layout')

@section('content')

<h2>Staff
    <a href="{!! route('export.staff') !!}" class="btn btn-default" title="Export as Excel">
        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
    </a>
</h2>
@include('report.partials.staff_list')
@stop