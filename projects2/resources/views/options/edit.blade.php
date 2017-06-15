@extends('layout')

@section('content')
    <div class="container">
        <h2>
            Site Options
        </h2>

        <form method="POST" action="{!! route('options.update') !!}">
            {!! csrf_field() !!}

            <div class="form-group">
                <label for="maximum_applications">Maximum number of students allowed to apply for a project</label>
                <input type="number" id="maximum_applications" name="maximum_applications" value="{{ $maximum_applications }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="required_choices">Required number of University of Glasgow projects a student must submit</label>
                <input type="number" id="required_choices" name="required_choices" value="{{ $required_choices }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="uestc_required_choices">Required number of UESTC projects a student must submit</label>
                <input type="number" id="uestc_required_choices" name="uestc_required_choices" value="{{ $uestc_required_choices }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="round">Current Round</label>
                <input type="number" id="round" name="round" value="{{ $round }}" class="form-control" required>
            </div>

            <div class="checkbox">
                <label>
                    <input type="hidden" name="logins_allowed" value="0">
                    <input type="checkbox" id="logins_allowed" name="logins_allowed" value="1" @if ($logins_allowed) checked @endif> Allow students to log in?
                </label>
            </div>

            <div class="checkbox">
                <label>
                    <input type="hidden" name="applications_allowed" value="0">
                    <input type="checkbox" id="applications_allowed" name="applications_allowed" value="1" @if ($applications_allowed) checked @endif> Allow students to apply for projects?
                </label>
            </div>

            <p></p>
            <button type="submit" class="btn btn-primary">Update</button>

        </form>
    </div>
@stop
