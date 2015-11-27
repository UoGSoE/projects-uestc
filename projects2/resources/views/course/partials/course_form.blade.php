            <input type="hidden" name="id" value="{{ $course->id }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="inputCode">Code</label>
                <input type="text" id="inputCode" name="code" value="{{ $course->code }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputTitle">Title</label>
                <input type="text" id="inputTitle" name="title" value="{{ $course->title }}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="inputLocation">Location</label>
                <select id="inputLocation" name="location_id" class="form-control">
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @if ($course->location_id == $location->id) selected @endif>
                            {{ $location->title }}
                        </option>
                    @endforeach
                </select>
            </div>
