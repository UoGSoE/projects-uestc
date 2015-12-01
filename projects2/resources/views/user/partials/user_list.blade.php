        <table class="table table-striped table-hover datatable">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Surname</th>
                    <th>Forenames</th>
                    <th>Email</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><a href="{!! action('UserController@show', $user->id) !!}">{{ $user->username }}</a></td>
                        <td>{{ $user->surname }}</td>
                        <td>{{ $user->forenames }}</td>
                        <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        <td>
                            {{ $user->password ? 'External' : 'Internal' }}
                            {{ $user->is_student ? 'Student' : 'Staff' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
