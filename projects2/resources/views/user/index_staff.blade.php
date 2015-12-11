@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Current Staff
            <a href="{!! action('UserController@create') !!}" class="btn btn-default">Add New Staff</a>
            <a href="{!! action('UserController@import') !!}" class="btn btn-default">Import Staff</a>
        </h2>
        @include('user.partials.user_list')
    </div>
    @include('partials.datatables', ['max' => 100])
@stop
