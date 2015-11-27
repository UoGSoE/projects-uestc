@extends('layout')

@section('content')

    <h2>
        Roles
        <a href="{!! action('RoleController@create') !!}" class="btn btn-default">
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
            @foreach ($roles as $role)
                <tr>
                    <td>
                        <a href="{!! action('RoleController@edit', $role->id) !!}">
                            {{ $role->title }}
                        </a>
                    </td>
                    <td>
                        {{ $role->label }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop