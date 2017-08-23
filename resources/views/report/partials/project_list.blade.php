    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Owner</th>
                <th>University</th>
                <th>Discipline</th>
                <th>1st round choices</th>
                @if (isset($excel))
                    <th>
                        Allocated?
                    </th>
                @endif
                <th>Project Description</th>
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
                        {{ $project->institution }}
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
                    <td applicants="round1-applicants-{{ $project->roundStudentCount(1) }}">
                        {{ $project->roundStudentCount(1) }}
                    </td>
                    @if (isset($excel))
                    <td>
                        {{ $project->numberAccepted() ? 'Y' : 'N' }}
                    </td>
                    @endif
                    <td>
                        {{ $project->description }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
