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
            <div class="form-group">
                <label for="inputRoles">Roles</label>
                <select id="inputRoles" name="roles" class="form-control" required>
                    <option value="0">Normal User</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @if ($user->hasRole($role->title)) selected @endif>
                            {{ $role->label }}
                        </option>
                    @endforeach
                </select>
            </div>
