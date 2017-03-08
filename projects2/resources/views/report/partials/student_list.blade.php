    <h2>Students</h2>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Name</th>
                @foreach (range(1, $requiredChoices) as $index)
                    <th></th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr class="">
                    <td @if ($student->unallocated()) class="bg-danger" @endif>
                        <a href="{!! action('UserController@show', $student->id) !!}">
                            {{ $student->fullName() }} ({{ $student->matric() }} 
                            {{ $student->course() ? $student->course()->code : 'N/A' }})
                        </a>
                    </td>
                    @foreach (range(0, $requiredChoices - 1) as $index)
                        <td>
                            @if ($student->projectsArray($index))
                                {{ $student->projectsArray($index)->title }}
                            @else
                                N/A
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
