@extends('layout')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>
                <i>{{ $helloWords[array_rand($helloWords)] }}</i>
                {{ Auth::user()->fullName() }}
            </h1>
        </div>
        <div>
            @if (Auth::user()->isStaff())
                @include ('project.staff_index', ['projects' => Auth::user()->projects])
            @else
                @include ('project.student_index')
            @endif
        </div>
    </div>
@stop
