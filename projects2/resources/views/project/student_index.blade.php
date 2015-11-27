<div class="page-header">
    <h1>
        <i>{{ $helloWords[array_rand($helloWords)] }}</i>
        {{ Auth::user()->fullName() }}
    </h1>
</div>
<h2>Available Projects</h2>
<p>
    Please choose five projects in order of preference.
</p>
<form method="POST" action="{!! action('UserController@chooseProjects') !!}">
{{ csrf_field() }}
@foreach (Auth::user()->availableProjects() as $project)
    <div class="panel panel-default">
        <div class="panel-heading fake-link">
            <h3 class="panel-title">
                {{ $project->title }} ({{ $project->owner->fullName() }})
            </h3>
        </div>
        <div class="panel-body" style="display: none">
            {{ $project->description }}
        </div>
        <div class="panel-footer" style="display: none">
            Preference :
            <label class="radio-inline">
                <input type="radio" name="first" value="{{ $project->id }}"> 1
            </label>
            <label class="radio-inline">
                <input type="radio" name="second" value="{{ $project->id }}"> 2
            </label>
            <label class="radio-inline">
                <input type="radio" name="third" value="{{ $project->id }}"> 3
            </label>
            <label class="radio-inline">
                <input type="radio" name="fourth" value="{{ $project->id }}"> 4
            </label>
            <label class="radio-inline">
                <input type="radio" name="fifth" value="{{ $project->id }}"> 5
            </label>
        </div>
    </div>
@endforeach
<button type="submit" class="btn btn-primary">Submit Choices</button>
<script>
$(document).ready(function() {
    $('.panel-title').click(function() {
        var parent = $(this).parent();
        parent.siblings().toggle();
    });
});
</script>