@extends('layout')

@section('content')
<h2>Profile for {{ $user->fullName() }}</h2>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Bio/introduction</h3>
  </div>
  <div class="panel-body">
    {{ $user->bio }}
  </div>
</div>

@if ($user->hasCV())
    <a href="" class="btn btn-default">Download their CV</a>
@endif

@endsection