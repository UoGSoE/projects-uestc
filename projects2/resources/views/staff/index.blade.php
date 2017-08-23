@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Current Staff
            <a href="{!! route('staff.create') !!}" class="btn btn-default">Add New Staff</a>
            <a href="{!! route('staff.import') !!}" class="btn btn-default">Import Staff</a>
        </h2>
        @include('user.partials.user_list')
    </div>
    @include('partials.datatables', ['max' => 100])
@stop
