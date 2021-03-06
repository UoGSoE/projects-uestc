@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Current Users
            <a href="{!! action('UserController@create') !!}" class="btn btn-default">Add New User</a>
        </h2>
        @include('user.partials.user_list')
    </div>
    @include('partials.datatables', ['max' => 100])
@stop
