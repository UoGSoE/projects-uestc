@extends('layout')

@section('content')
    <h2>Disciplines</h2>
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
                    <td>{{ $discipline->title }}</td>
                    <td>0</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

