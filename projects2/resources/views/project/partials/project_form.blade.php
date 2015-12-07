        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $project->id }}">
        <div class="form-group">
            <label for="inputTitle">Title</label>
            <input type="text" id="inputTitle" name="title" value="{{ $project->title }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="inputDescription">Description</label>
            <textarea id="inputDescription" name="description" class="form-control" rows="7" required>{{ $project->description }}</textarea>
        </div>
        <div class="form-group">
            <label for="inputPrereq">Prerequisite Skills</label>
            <textarea id="inputPrereq" name="prereq" class="form-control" rows="7">{{ $project->prereq }}</textarea>
        </div>
        <div class="checkbox">
            <label>
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @if ($project->is_active) checked @endif> Project is active?
            </label>
        </div>
        <div class="form-group">
            <label for="inputProgrammes">Programmes</label>
            <select id="inputProgrammes" name="programmes[]" class="form-control select2" multiple required>
                @foreach ($programmes as $programme)
                    <option value="{{ $programme->id }}" @if ($project->hasProgramme($programme->id)) selected @endif>
                        {{ $programme->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="inputCourses">Courses</label>
            <select id="inputCourses" name="courses[]" class="form-control select2" multiple required>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @if ($project->hasCourse($course->id)) selected @endif>
                        {{ $course->code }} {{ $course->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="inputType">Type</label>
            <select id="inputType" name="type_id" class="form-control" required>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @if ($project->type_id == $type->id) selected @endif>
                        {{ $type->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="inputMaximumStudents">Maximum Students</label>
            <input type="number" id="inputMaximumStudents" name="maximum_students" value="{{ $project->maximum_students }}" class="form-control" required min="1">
        </div>
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
            <input type="hidden" name="user_id" value="{{ $project->user_id or Auth::user()->id }}">
        @endcan