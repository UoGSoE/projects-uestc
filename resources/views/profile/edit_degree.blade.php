@extends('layout')

@section('content')
<h2>Degree Type</h2>
<p>
    Please select your degree type from this drop down.
</p>
<form method="POST" action="{{ route('student.profile_update_degree') }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="bio" value="{{ Auth::user()->bio }}">
    <div class="form_group">
        <label for="degree_type">Degree type</label>
        <select name="degree_type">
            <option value="" disabled>Please select...</option>
            <option value="Single" @if (Auth::user()->degree_type == 'Single') selected @endif>Single degree</option>
            <option value="Dual" @if (Auth::user()->degree_type == 'Dual') selected @endif>Dual degree</option>
        </select>
    </div>
    <button type="submit" class="btn btn-default">Update</button>
</form>
@endsection
