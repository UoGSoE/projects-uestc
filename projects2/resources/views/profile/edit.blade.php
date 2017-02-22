@extends('layout')

@section('content')
<h2>Your Profile</h2>
<form method="POST" action="{{ route('student.profile_update') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="bio">Your brief bio/introduction</label>
        <textarea name="bio" class="form-control" rows="5">{{ Auth::user()->bio }}</textarea>
    </div>
    <button type="submit" class="btn btn-default">Update</button>
</form>
@endsection
