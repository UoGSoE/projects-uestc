    <h2>Students</h2>
    <table class="table table-striped table-hover datatable">
        <thead>
            <tr>
                <th>Name</th>
                <th>1st</th>
                <th>2nd</th>
                <th>3rd</th>
                <th>4th</th>
                <th>5th</th>
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
                    @for ($i = 1; $i <= 5; $i++)
                        <td>
                            @if ($student->projectChoice($i))
                                <a href="{!! action('ProjectController@show', $student->projectChoice($i)->id) !!}"
                                    title="{{ $student->projectChoice($i)->owner->fullName() }}"
                                >
                                    {{ $student->projectChoice($i)->title }}
                                </a>
                                @if ($student->projectChoice($i)->pivot->accepted)
                                    <span class="glyphicon glyphicon-ok" title="Accepted">
                                @endif
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
