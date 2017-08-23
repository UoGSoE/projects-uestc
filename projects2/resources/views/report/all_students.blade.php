@extends('layout')

@section('content')

<h2>
    Students
    <a href="{!! route('export.students') !!}" class="btn btn-default" title="Export as Excel">
        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
    </a>
</h2>
@include('report.partials.student_list')
@include('partials.datatables')
@stop