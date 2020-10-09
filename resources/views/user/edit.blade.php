@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit <a href="{!! route('user.show', $user->id) !!}">{{ $user->fullName() }}</a>
            <a action-href="{!! route('user.destroy', $user->id) !!}" data-confirm="Really delete this user? This will also delete ALL of their projects!" class="btn btn-danger pull-right data-confirm">
                Delete
            </a>
        </h2>

        <form method="POST" action="{!! route('user.update', $user->id) !!}">

            @include ('user.partials.user_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
