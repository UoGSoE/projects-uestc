<table class="table table-striped table-hover datatable">
    <thead>
        <tr>
            <th>GUID</th>
            <th>Name</th>
            @for ($i = 1; $i <= $required['uestc']; $i++)
                <th>UESTC Choice {{ $i }}</th>
            @endfor
            @for ($i = 1; $i <= $required['uog']; $i++)
                <th>UoG Choice {{ $i }}</th>
            @endfor
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
                @foreach ($student->projects()->UESTC()->orderBy('preference')->get() as $project)
                    <td>
                        ({{ $project->institution }}) {{ $project->title }}
                    </td>
                @endforeach
                @foreach ($student->projects()->UoG()->orderBy('preference')->get() as $project)
                    <td>
                            ({{ $project->institution }}) {{ $project->title }}
                    </td>
                @endforeach
                @for ($i = ($required['uestc'] + $required['uog']) - $student->projects()->count(); $i > 0; $i--)
                    <td></td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>