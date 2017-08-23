    <div class="panel panel-default">
        <div class="panel-heading fake-link" id="title_{{ $project->id }}">
            <h3 class="panel-title">
                {{ $project->title }} ({{ $project->owner->fullName() }})
                @if ($project->discipline)
                    (field {{ $project->disciplineTitle() }})
                @endif
            </h3>
        </div>
        <div class="panel-body" >
            {{ $project->description }}
            <div class="help-block">
                Prerequisites: {{ $project->prereq }}
            </div>
        </div>
        <ul class="list-group">
            @foreach ($project->links as $link)
                <li class="list-group-item">
                    <a href="{{ $link->url }}" target="_blank">
                        {{ $link->url }}
                    </a>
                </li>
            @endforeach
            @foreach ($project->files as $file)
                <li class="list-group-item">
                    <a href="/projectfile/{{ $file->id }}">
                        <span class="glyphicon glyphicon-download" aria-hidden="true"></span> {{ $file->original_filename }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>