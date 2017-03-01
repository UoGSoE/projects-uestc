@extends('layout')

@section('content')

    <h2>Bulk Allocation</h2>
    <p>
        This page lets you bulk-allocate students who have not yet been allocated to a project.
    </p>
    <form method="POST" action="{!! route('bulkallocate.update') !!}">
    {!! csrf_field() !!}
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Student</th>
                @foreach (range(1, config('projects.requiredProjectChoices')) as $index)
                    <th></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                @if ($student->unallocated())
                    <tr>
                        <td>{{ $student->fullName() }} ({{ $student->matric() }} {{ $student->course() ? $student->course()->code : 'N/A' }})</td>
                        @for ($i = 0; $i < config('projects.requiredProjectChoices'); $i++)
                            <td>
                                {!! dd($student->projectsArray()) !!}
                                @if ($student->projectsArray($i))
                                    @if ($student->projectsArray($i)->isAvailable())
                                        <input type="radio" name="student[{{$student->id}}]" value="{{ $student->projectChoice($i)->id }}">
                                    @else
                                        (Full)
                                    @endif
                                    <a href="{!! route('project.show', $student->projectsArray($i)->id) !!}"
                                        title="{{ $student->projectsArray($i)->owner->fullName() }} - {{ $student->projectsArray($i)->maximum_students }} max"
                                    >
                                        {{ $student->projectsArray($i)->title }}
                                    </a>
                                @else
                                    WTF?
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
