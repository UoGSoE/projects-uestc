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
