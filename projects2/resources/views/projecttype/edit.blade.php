@extends('layout')

@section('content')

    <h2>
        Edit Project Type
        <a action-href="{!! action('ProjectTypeController@destroy', $type->id) !!}" data-confirm="Really delete this type? This will remove ALL projects of this type!" class="btn btn-danger pull-right data-confirm">
            Delete
        </a>
    </h2>
    <form method="POST" action="{!! action('ProjectTypeController@update', $type->id) !!}">
        <input type="hidden" name="_method" value="PATCH">
        @include('projecttype.partials.type_form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@stop