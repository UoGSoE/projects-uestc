@extends('layout')

@section('content')

    <h2>Bulk Allocation</h2>
    <p>
        This page lets you bulk-allocate students who have not yet been allocated to a project.
    </p>
    <form method="POST" action="{!! action('ProjectController@bulkAllocate') !!}">
    {!! csrf_field() !!}
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Student</th>
                <th>1st</th>
                <th>2nd</th>
                <th>3rd</th>
                <th>4th</th>
                <th>5th</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                @if ($student->unallocated())
                    <tr>
                        <td>{{ $student->fullName() }} ({{ $student->matric() }})</td>
                        @for ($i = 1; $i <= 5; $i++)
                            <td>
                                @if ($student->projectChoice($i))
                                    <a href="{!! action('ProjectController@show', $student->projectChoice($i)->id) !!}"
                                        title="{{ $student->projectChoice($i)->owner->fullName() }} - {{ $student->projectChoice($i)->maximum_students }} max"
                                    >
                                        {{ $student->projectChoice($i)->title }}
                                    </a>
                                    @if ($student->projectChoice($i)->isAvailable())
                                        <input type="radio" name="student[{{$student->id}}]" value="{{ $student->projectChoice($i)->id }}">
                                    @else
                                        (Full)
                                    @endif
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary pull-right">Allocate Choices</button>
    </form>
    @include('partials.datatables', ['max' => 100])
@stop
