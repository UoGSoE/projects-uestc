    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Preallocated?</th>
                <th>1st Round</th>
                <th>2nd Round</th>
                <th>Project</th>
                <th>Supervisor</th>
                <th>Discipline</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr class="">
                    <td @if ($student->unallocated()) class="bg-danger" @endif>
                        <a href="{!! route('user.show', $student->id) !!}">
                            {{ $student->fullName() }} ({{ $student->username }})
                        </a>
                    </td>
                    <td>
                        @if ($student->isAllocated())
                            @if ($student->allocatedProject()->manually_allocated)
                                <span>
                                    Y
                                </span>
                            @else
                                N
                            @endif
                        @else
                            N
                        @endif
                    </td>
                    <td data="round_1_student_{{ $student->id}}_accepted_{{ $student->acceptedOnRound(1) }}">
                        @if ($student->acceptedOnRound(1))
                            Y
                        @else
                            N
                        @endif
                    </td>
                    <td data="round_2_student_{{ $student->id}}_accepted_{{ $student->acceptedOnRound(2) }}">
                        @if ($student->acceptedOnRound(2))
                            Y
                        @else
                            N
                        @endif
                    </td>
                    @if ($student->unallocated())
                        <td></td>
                        <td></td>
                        <td></td>
                    @else
                        <td>
                            {{ $student->allocatedProject()->title }}
                            @if ($student->allocatedProject()->manually_allocated)
                                (Pre)
                            @endif
                        </td>
                        <td>
                            {{ $student->allocatedProject()->owner->fullName() }}
                        </td>
                        <td>
                            {{ $student->allocatedProject()->disciplineTitle() }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
