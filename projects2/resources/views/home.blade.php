@extends('layout')

@section('content')
    <div class="container">
        <div>
            @if (Auth::user()->isStaff())
                @include ('project.staff_index', ['projects' => Auth::user()->projects])
            @else
                @include ('project.student_index')
            @endif
        </div>
    </div>
@stop
