<div class="page-header">
    <h1>
        <i>Hello</i>
        {{ Auth::user()->fullName() }}
    </h1>
</div>
<h2>Available Projects</h2>
<p>
    Please choose {{ config('projects.requiredProjectChoices') }} projects in order of preference.
</p>
<form method="POST" action="{!! route('choices.update') !!}" id="vueform">
    {{ csrf_field() }}
    @foreach (Auth::user()->availableProjects() as $project)
        @if ($project->isAvailable())
            <div class="panel panel-default {{ $project->discipline->cssTitle() }}>
                <div class="panel-heading fake-link">
                    <h3 class="panel-title">
                        {{ $project->title }} ({{ $project->owner->fullName() }}) (field {{ $project->discipline->title }})
                    </h3>
                </div>
                <div class="panel-body" style="display: none">
                    {{ $project->description }}
                    @if ($project->links()->count() > 0)
                        Links:
                        <ul>
                            @foreach ($project->links as $link)
                                <li><a href="{{ $link->url }}">{{ $link->url }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="help-block">
                        Prerequisites: {{ $project->prereq or 'None' }}
                    </div>
                </div>
                @if ($applicationsEnabled)
                    <div class="panel-footer" style="display: none">
                        <label class="radio-inline">
                            <input type="radio" id="project{{ $project->id }}_1" name="choices[]" v-model="first" value="{{ $project->id }}"> Apply
                        </label>
                    </div>
                @endif
            </div>
        @endif
    @endforeach
    <button type="submit" id="submit" class="btn btn-primary" :disabled="!choicesAreOk">
        <span v-if="choicesAreOk">Submit Choices</span>
        <span v-else>Choose {{ config('projects.requiredProjectChoices') }} Different Choices</span>
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
            fifth: null,
            required: {{ $requiredProjectChoices }}
        },
        computed: {
            chosenRequiredAmount: function() {
                return this.first && this.second;
            },
            allDifferent: function() {
                return this.first != this.second;
            },
            choicesAreOk: function() {
                return this.chosenRequiredAmount && this.allDifferent;
            }
        }
    });
</script>