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
            @can('edit_user_roles')
                <div class="form-group">
                    <label for="inputRoles">Roles (Optional)</label>
                    <select id="inputRoles" name="roles[]" class="form-control select2" multiple>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @if ($user->hasRole($role->title)) selected @endif>
                                {{ $role->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endcan
        @if ($user->is_student)
            <div class="form-group">
                <label for="inputProject">Allocate to project</label>
                <select id="inputProject" name="project_id" class="form-control">
                    <option value="">No Change</option>
                    @foreach($projects as $project)
                        @if ($project->isAvailable())
                            <option value="{{ $project->id }}">
                                {{ $project->title }} ({{$project->type->title}})
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="help-block">
                    This will allocate them as their first preference and automatically approve them.
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