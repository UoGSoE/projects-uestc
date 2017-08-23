@extends('layout')

@section('content')
    <div class="container">
        <h2>
            New User
        </h2>

        <form method="POST" action="{!! route('user.store') !!}">

            @include ('user.partials.user_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Create</button>

        </form>
    </div>
@stop
