@extends('layout')

@section('content')

    <h2>
        Bulk Allocation
        <a href="{!! route('allocations.import') !!}" class="btn btn-default">Import Allocations</a>
        <a action-href="{!! route('admin.get_clear_unsuccessful') !!}" data-confirm="Really remove all un-successful applications" class="btn btn-danger pull-right data-confirm">
            Remove All Unsuccesfull Applications
        </a>

    </h2>
    <p>
        This page lets you bulk-allocate students who have not yet been allocated to a project.
    </p>
    <form method="POST" action="{!! route('bulkallocate.update') !!}">
    {!! csrf_field() !!}
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Student</th>
                @foreach (range(1, $requiredChoices) as $index)
                    <th></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                @if ($student->unallocated())
                    <tr>
                        <td>{{ $student->fullName() }} ({{ $student->matric() }} {{ $student->course() ? $student->course()->code : 'N/A' }})</td>
                        @for ($i = 0; $i < $requiredChoices; $i++)
                            <td>
                                @if (!is_null($student->projectsArray($i)))
                                    @if ($student->projectsArray($i)->isAvailable())
                                        <input type="radio" name="student[{{$student->id}}]" value="{{ $student->projectsArray($i)->id }}">
                                    @else
                                        (Full)
                                    @endif
                                    <a href="{!! route('project.show', $student->projectsArray($i)->id) !!}"
                                        title="{{ $student->projectsArray($i)->owner->fullName() }} - {{ $student->projectsArray($i)->maximum_students }} max"
                                    >
                                        {{ $student->projectsArray($i)->title }}
                                    </a>
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
