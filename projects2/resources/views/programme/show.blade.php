@extends('layout')

@section('content')

    <h2>
        Details for {{ $programme->title }}
        <a href="{!! action('ProgrammeController@edit', $programme->id) !!}" class="btn btn-default">Edit</a>
    </h2>
    <dl>
        <dt>Title</dt>
        <dd>{{ $programme->title }}</dd>
    </dl>
@stop