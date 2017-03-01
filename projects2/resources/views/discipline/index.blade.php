@extends('layout')

@section('content')
    <h2>
        Disciplines
        <a href="{!! route('discipline.create') !!}" class="btn btn-default">
            Add New Discipline
        </a>
    </h2>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Title</th>
                <th>No. Projects</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($disciplines as $discipline)
                <tr>
                    <td>
                        <a href="{!! route('discipline.edit', $discipline->id) !!}">
                            {{ $discipline->title }}
                        </a>
                    </td>
                    <td>0</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

