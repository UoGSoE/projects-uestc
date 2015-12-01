@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Programme {{ $programme->title }}
            <a action-href="{!! action('ProgrammeController@destroy', $programme->id) !!}" data-confirm="Really delete this programme?" class="btn btn-danger pull-right data-confirm">
                Delete
            </a>

        </h2>

        <form method="POST" action="{!! action('ProgrammeController@update', $programme->id) !!}">
            <input type="hidden" name="_method" value="PATCH">

            @include ('programme.partials.programme_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
