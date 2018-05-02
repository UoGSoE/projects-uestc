@extends('layout')

@section('content')

<h2>
    Students
    <a href="{!! route('export.students') !!}" class="btn btn-default" title="Export as Excel">
        Export All Students
        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
    </a>
    <a href="{!! route('export.students.single') !!}" class="btn btn-default" title="Export as Excel">
        Export Single Degree Students
        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
    </a>
    <a href="{!! route('export.students.dual') !!}" class="btn btn-default" title="Export as Excel">
        Export Dual Degree Students
        <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
    </a>
</h2>
@include('report.partials.student_list')
@include('partials.datatables')
@stop