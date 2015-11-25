@extends('layout')

@section('content')
    <div class="container">
    	@if(count($errors) > 0)
        	<div class="alert alert-danger">
        		{{ $errors }}
        	</div>
    	@endif
        <div class="page-header">
            <h1>
                <i>{{ $helloWords[array_rand($helloWords)] }}</i>
                {{ Auth::user()->fullName() }}
            </h1>
        </div>
    </div>
@stop
