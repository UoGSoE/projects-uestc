@extends('layout')

@section('content')
<div class="container">
    <h2>Import Student Allocations</h2>
    <p>This page allows you to upload a spreadsheet of students with their final allocations for projects. This will email the students with their project allocation.</p>
    <form method="POST" action="{{ route('allocations.do_import') }}" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="input-group">
            <label class="input-group-btn">
                <span class="btn btn-primary">
                    Browse&hellip; <input type="file" name="allocations" style="display: none;">
                </span>
            </label>
            <input type="text" class="form-control" readonly>
        </div>
        <p class="help-block">
            Spreadsheet must in of the form <pre>GUID|Name|Project</pre>
        </p>
        <button type="submit" class="btn btn-success" disabled>Submit</button>
    </form>
@include('partials.fileupload')
@stop