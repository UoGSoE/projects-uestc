    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Project Title</th>
                <th>Owner</th>
                <th>Sup. Name</th>
                <th>Sup. Email</th>
                <th>University</th>
                <th>Disciplines</th>
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
                        <a href="{!! route('project.show', $project->id) !!}">
                            {{ $project->title }}
                        </a>
                        @if (!$project->is_active) </del> @endif
                    </td>
                    <td>
                        <a href="{!! route('user.show', $project->owner->id) !!}">
                            {{ $project->owner->fullName() }}
                        </a>
                    </td>
                    <td>
                        {{  $project->supervisor_name }}
                    </td>
                    <td>
                        {{  $project->supervisor_email }}
                    </td>
                    <td>
                        {{ $project->institution }}
                    </td>
                    <td>
                        @if ($project->discipline_id)
                            <a href="{!! route('report.projects_of_discipline', $project->discipline_id) !!}">
                                {{ $project->discipline->title }}
                            </a>
                        @elseif ($project->disciplines->count())
                            <ul class="list-inline">
                                @foreach ($project->disciplines as $discipline)
                                    <li>
                                        <a href="{!! route('report.projects_of_discipline', $discipline->id) !!}">
                                            {{ $discipline->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            N/A
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
