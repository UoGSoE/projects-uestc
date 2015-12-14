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
<form method="POST" action="{!! action('UserController@chooseProjects') !!}" id="vueform">
{{ csrf_field() }}
@foreach (Auth::user()->availableProjects() as $project)
    @if ($project->isAvailable())
    <div class="panel panel-default anyprogramme @foreach($project->programmes as $programme) {{md5($programme->title)}} @endforeach">
        <div class="panel-heading fake-link">
            <h3 class="panel-title">
                {{ $project->title }} ({{ $project->owner->fullName() }})
            </h3>
        </div>
        <div class="panel-body" style="display: none">
            {{ $project->description }}
            <div class="help-block">
                Prerequisites: {{ $project->prereq or 'None' }}
            </div>
        </div>
        <div class="panel-footer" style="display: none">
            Preference :
            <label class="radio-inline">
                <input type="radio" name="choice[1]" v-model="first" value="{{ $project->id }}"> 1
            </label>
            <label class="radio-inline">
                <input type="radio" name="choice[2]" v-model="second" value="{{ $project->id }}"> 2
            </label>
            <label class="radio-inline">
                <input type="radio" name="choice[3]" v-model="third" value="{{ $project->id }}"> 3
            </label>
            <label class="radio-inline">
                <input type="radio" name="choice[4]" v-model="fourth" value="{{ $project->id }}"> 4
            </label>
            <label class="radio-inline">
                <input type="radio" name="choice[5]" v-model="fifth" value="{{ $project->id }}"> 5
            </label>
        </div>
    </div>
    @endif
@endforeach
<button type="submit" class="btn btn-primary" :disabled="!choicesAreOk">
    <span v-if="choicesAreOk">Submit Choices</span>
    <span v-else>Choose 5 Different Choices</span>
</button>
</form>
<script src="vendor/vue.min.js"></script>
<script>
    $(document).ready(function() {
        $('.panel-title').click(function() {
            var parent = $(this).parent();
            parent.siblings().toggle();
        });
        $('#inputProgramme').change(function() {
            var value = $(this).val();
            $('.panel, .' + value).show();
            $('.panel').not('.' + value).hide();
        });
    });
    new Vue({
        el: '#vueform',
        data: {
            first: null,
            second: null,
            third: null,
            fourth: null,
            fifth: null
        },
        computed: {
            chosenFive: function() {
                return this.first && this.second;
            },
            allDifferent: function() {
                return this.first != this.second;
            },
            choicesAreOk: function() {
                return this.chosenFive && this.allDifferent;
            }
        }
    });
</script>