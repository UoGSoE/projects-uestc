<table class="table table-striped table-hover datatable">
    <thead>
        <tr>
            <th>Name</th>
            <th>University</th>
            <th>Projects</th>
            <th>Active Projects</th>
            <th>Inactive Projects</th>
            <th>Applied</th>
            <th>Accepted</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>
                    @can('view_users')
                        <a href="{!! action('UserController@show', $user->id) !!}">
                            {{ $user->fullName() }}
                        </a>
                    @else
                        {{ $user->fullName() }}
                    @endif
                </td>
                <td>{{ $user->institution }}</td>
                <td>{{ $user->projects->count() }}</td>
                <td>{{ $user->activeProjects->count() }}</td>
                <td>{{ $user->inactiveProjects->count() }}</td>
                <td>{{ $user->totalStudents() }}</td>
                <td>{{ $user->totalAcceptedStudents() }}</td>
            </tr>
        @endforeach
    </tbody>
</table>