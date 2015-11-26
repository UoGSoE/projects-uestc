@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Edit Programme {{ $programme->title }}
            <form method="POST" action="{!! action('ProgrammeController@destroy', $programme->id) !!}" class="pull-right">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" value="Delete" class="btn btn-danger" data-toggle="modal" data-target="#confirm-model">Delete</button>
            </form>

        </h2>

        <form method="POST" action="{!! action('ProgrammeController@update', $programme->id) !!}">
            <input type="hidden" name="_method" value="PATCH">

            @include ('programme.partials.programme_form')

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
