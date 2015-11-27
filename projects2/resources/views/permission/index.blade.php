@extends('layout')

@section('content')

    <h2>
        Permissions
        <a href="{!! action('PermissionController@create') !!}" class="btn btn-default">
            Add New
        </a>
    </h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Label</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permissions as $permission)
                <tr>
                    <td>
                        <a href="{!! action('PermissionController@edit', $permission->id) !!}">
                            {{ $permission->title }}
                        </a>
                    </td>
                    <td>{{ $permission->label }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
