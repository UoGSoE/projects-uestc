@extends('layout')

@section('content')

    <h2>
        Bulk Preallocation
    </h2>
    <p>
        This page lets you bulk-preallocate students to projects.
    </p>
    <form method="POST" action="{!! route('bulkpreallocate.update') !!}">
    {!! csrf_field() !!}
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Project</th>
                <th>Student</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>
                        {{ $project->title }}
                    </td>
                    <td>
                        <div class="form-group">
                            <select id="project_{{ $project->id }}" name="project[{{ $project->id}}]" class="form-control">
                                <option value=""></option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">
                                        {{ $student->fullName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary pull-right">Allocate Choices</button>
    </form>
    @include('partials.datatables')
@stop
