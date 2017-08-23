    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>GUID</th>
                <th>Name</th>
                <th>Choice 1</th>
                <th>Choice 2</th>
                <th>Choice 3</th>
                <th>Choice 4</th>
                <th>Choice 5</th>
                <th>Choice 6</th>
                <th>Choice 7</th>
                <th>Choice 8</th>
                <th>Choice 9</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr class="">
                    <td>
                        {{ $student->username }}
                    </td>
                    <td @if ($student->unallocated()) class="bg-danger" @endif>
                        <a href="{!! route('user.show', $student->id) !!}">
                            {{ $student->fullName() }}
                        </a>
                    </td>
                    @foreach ($student->projects()->get() as $project)
                        <td>
                             {{ $project->title }}
                        </td>
                    @endforeach
                    @for ($i = 9 - $student->projects()->count(); $i > 0; $i--)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
