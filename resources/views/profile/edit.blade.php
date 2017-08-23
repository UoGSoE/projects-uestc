@extends('layout')

@section('content')
<h2>Your Profile</h2>
<form method="POST" action="{{ route('student.profile_update') }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="bio">Your brief bio/introduction</label>
        <textarea name="bio" class="form-control" rows="5">{{ Auth::user()->bio }}</textarea>
    </div>
    <div class="form-group">
        <label for="cv">Attach a CV</label>
        <input type="file" id="cv" name="cv">
        <p class="help-block">This should be a PDF or Word Document</p>
      </div>
    <button type="submit" class="btn btn-default">Update</button>
</form>
@endsection
