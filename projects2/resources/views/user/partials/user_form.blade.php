            <input type="hidden" name="id" value="{{ $user->id }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="inputUsername">Username</label>
                <input type="text" id="inputUsername" name="username" value="{{ $user->username }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputSurname">Surname</label>
                <input type="text" id="inputSurname" name="surname" value="{{ $user->surname }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputForenames">Forenames</label>
                <input type="text" id="inputForenames" name="forenames" value="{{ $user->forenames }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputEmail">Email</label>
                <input type="email" id="inputEmail" name="email" value="{{ $user->email }}" class="form-control" required>
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
                    <input type="checkbox" name="is_student" value="1" @if ($user->is_student) checked @endif> Is a student?
                </label>
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
        <div class="form-group">
            <label for="inputLocation">Location</label>
            <select id="inputLocation" name="location_id" class="form-control" required>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @if ($user->location_id == $location->id) selected @endif>
                        {{ $location->title }}
                    </option>
                @endforeach
            </select>
        </div>
        @if ($user->is_student)
            <div class="form-group">
                <label for="inputProject">Allocate to project</label>
                <select id="inputProject" name="project_id" class="form-control">
                    <option value="">No Change</option>
                    @foreach($projects as $project)
                        @if ($project->isAvailable())
                            <option value="{{ $project->id }}">
                                {{ $project->title }} ({{$project->type->title}} - {{ $project->location->title }})
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
            });
        </script>