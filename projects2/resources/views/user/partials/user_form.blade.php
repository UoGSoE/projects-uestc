            <input type="hidden" name="id" value="{{ $user->id }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="inputUsername">Username</label>
                <input type="text" id="inputUsername" name="username" value="{{ old('username', $user->username) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputSurname">Surname</label>
                <input type="text" id="inputSurname" name="surname" value="{{ old('surname', $user->surname) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputForenames">Forenames</label>
                <input type="text" id="inputForenames" name="forenames" value="{{ old('forenames', $user->forenames) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputEmail">Email</label>
                <input type="email" id="inputEmail" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputPassword">Password</label>
                <input type="password" id="inputPassword" name="password" value="" class="form-control">
                <p class="help-block">
                    <b>Note:</b> Only fill this in if the user is <em>external</em> to the University. It must
                    be at least 12 characters long.
                </p>
            </div>
            <div class="checkbox">
                <label>
                    <input type="hidden" name="is_student" value="0">
                    <input type="checkbox" id="is_student" name="is_student" value="1" @if ($user->is_student) checked @endif> Is a student?
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="hidden" name="is_admin" value="0">
                    <input type="checkbox" id="is_admin" name="is_admin" value="1" @if ($user->is_admin) checked @endif> Is a site admin?
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="hidden" name="is_convenor" value="0">
                    <input type="checkbox" id="is_convenor" name="is_convenor" value="1" @if ($user->is_convenor) checked @endif> Is a project convenor?
                </label>
            </div>

            <div class="form-group" id="course_select" @if (!$user->is_student) style="display:none" @endif>
                <label for="inputCourse">Course</label>
                <select id="inputCourse" name="course_id" class="form-control">
                    <option value="">None</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @if ($user->course() and $user->course()->id == $course->id) selected @endif>
                            {{ $course->code }} - {{ $course->title }} 
                        </option>
                    @endforeach
                </select>
            </div>
        @if ($user->is_student and $user->unallocated())
            <div class="form-group">
                <label for="inputProject">Allocate to project</label>
                <select id="inputProject" name="project_id" class="form-control">
                    <option value="">No Change</option>
                    @foreach($projects as $project)
                        @if ($project->isAvailable())
                            <option value="{{ $project->id }}">
                                {{ $project->title }} ({{$project->disciplineTitle()}})
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="help-block">
                    This will also automatically approve them on the project.
                </p>
            </div>
        @endif
        <script>
            $(document).ready(function() {
                $('.select2').select2();
                $('#is_student').change(function() {
                    if($(this).is(":checked")) {
                        $("#course_select").fadeIn('fast');
                    } else {
                        $("#course_select").fadeOut('fast');
                    }
                });
            });
        </script>