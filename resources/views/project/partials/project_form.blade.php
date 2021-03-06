        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $project->id }}">
        <div class="form-group">
            <label for="inputTitle">Title</label>
            <input type="text" id="inputTitle" name="title" value="{{ old('title', $project->title) }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="inputDescription">Description</label>
            <textarea id="inputDescription" name="description" class="form-control" rows="7" required>{{ old('description',$project->description) }}</textarea>
        </div>
        <div class="form-group">
            <label for="inputPrereq">Prerequisite Skills</label>
            <textarea id="inputPrereq" name="prereq" class="form-control" rows="7">{{ old('prereq', $project->prereq) }}</textarea>
        </div>
        <div class="checkbox">
            <label>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @if ($project->is_active) checked @endif> Project is active?
            </label>
        </div>
        <div class="form-group">
            <label for="inputCourses">Courses</label>
            <select style="margin:28px !important" id="inputCourses" name="courses[]" class="form-control select2" multiple required>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @if ($project->hasCourse($course->id)) selected @endif>
                        {{ $course->code }} {{ $course->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="inputDisc">Disciplines</label>
            <select style="margin:28px !important" id="inputDisc" name="disciplines[]" class="form-control select2" multiple>
                @foreach ($disciplines as $discipline)
                    <option value="{{ $discipline->id }}" @if ($project->discipline_id == $discipline->id || $project->disciplines->contains('id', $discipline->id)) selected @endif>
                        {{ $discipline->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" id="institute_select">
            <label for="institution">Institution</label>
            <select id="institution" name="institution" class="{{$project->getInstitution()}} form-control">
                    <option value="UoG" @if ($project->getInstitution() == 'UoG') selected @endif>
                        UoG
                    </option>
                    <option value="UESTC" @if ($project->getInstitution() == 'UESTC') selected @endif>
                        UESTC
                    </option>
            </select>
        </div>

        <div class="form-group">
            <label for="inputSupervisorName">Supervisor Name</label>
            <input type="text" id="inputSupervisorName" name="supervisor_name" value="{{ old('supervisor_name', $project->supervisor_name ? $project->supervisor_name : auth()->user()->fullName()) }}" class="form-control">
        </div>

        <div class="form-group">
            <label for="inputSupervisorEmail">Supervisor Email</label>
            <input type="text" id="inputSupervisorEmail" name="supervisor_email" value="{{ old('supervisor_email', $project->supervisor_email ? $project->supervisor_email : auth()->user()->email) }}" class="form-control">
        </div>

        <input type="hidden" name="maximum_students" value="1">
        @can('edit_projects')
            <div class="form-group">
                <label for="inputOwner">Run By</label>
                <select id="inputOwner" name="user_id" class="form-control" required>
                    @foreach ($staff as $user)
                        <option value="{{ $user->id }}" @if ($project->user_id == $user->id) selected @endif>
                            {{ $user->fullName() }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="user_id" value="{{ $project->user_id ?? Auth::user()->id }}">
        @endcan
        @if ($project->files()->count() > 0)
            @foreach ($project->files as $file)
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="deletefiles[{{$file->id}}]" value="{{ $file->id }}"> Remove file <a href="{!! route('projectfile.download', $file->id) !!}">{{ $file->original_filename }}</a>?
                    </label>
                </div>
            @endforeach
        @endif
        <div class="form-group">
            <label>Add new files</label>
        </div>
        @foreach (range(1, 3) as $counter)
            <div class="form-group">
                <input type="file" name="files[]" multiple>
            </div>
        @endforeach

        @if ($project->links()->count() > 0)
            @foreach ($project->links as $link)
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="deletelinks[{{$link->id}}]" value="{{ $link->id }}"> Remove link to <a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a>?
                    </label>
                </div>
            @endforeach
        @endif
        <div class="form-group">
            <label>Add new links</label>
        </div>
        @foreach (range(1, 3) as $counter)
            <div class="form-group">
                <input class="form-control" type="input" name="links[][url]" multiple>
            </div>
        @endforeach
