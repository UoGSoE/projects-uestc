    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Title</th>
                <th>Owner</th>
                <th>Discipline</th>
                <th>Preallocated</th>
                <th>1st round choices</th>
                <th>Rresult</th>
                <th>2nd round choices</th>
                <th>Result</th>
                <th>Student</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr class="active_{{ $project->is_active }}">
                    <td>
                        @if (!$project->is_active) <del title="Not running"> @endif
                        <a href="{!! action('ProjectController@show', $project->id) !!}">
                            {{ $project->title }}
                        </a>
                        @if (!$project->is_active) </del> @endif
                    </td>
                    <td>
                        <a href="{!! action('UserController@show', $project->owner->id) !!}">
                            {{ $project->owner->fullName() }}
                        </a>
                    </td>
                    <td>
                        @if ($project->discipline)
                            <a href="{!! action('ReportController@allProjectsOfDiscipline', $project->discipline->id) !!}">
                                {{ $project->disciplineTitle() }}
                            </a>
                        @else
                            {{ $project->disciplineTitle() }}
                        @endif
                    </td>
                    <td>
                        @if ($project->manually_allocated)
                            <span class="preallocated-{{ $project->acceptedStudents()->first()->id }}">
                                Y
                            </span>
                        @else
                            N
                        @endif
                    </td>
                    <td applicants="round1-applicants-{{ $project->roundStudentCount(1) }}">
                        {{ $project->roundStudentCount(1) }}
                    </td>
                    <td applicants="round1-accepted-{{ $project->roundStudentAcceptedCount(1) }}">
                        @if ($project->roundStudentAcceptedCount(1) > 0)
                            Y
                        @else
                            N
                        @endif
                    </td>
                    <td applicants="round2-applicants-{{ $project->roundStudentCount(2) }}">
                        {{ $project->roundStudentCount(2) }}
                    </td>
                    <td applicants="round2-accepted-{{ $project->roundStudentAcceptedCount(2) }}">
                        @if ($project->roundStudentAcceptedCount(2) > 0)
                            Y
                        @else
                            N
                        @endif
                    </td>
                    <td>
                        @if ($project->numberAccepted() > 0)
                            @foreach ($project->acceptedStudents()->get() as $student)
                                {{ $student->fullname() }}
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
