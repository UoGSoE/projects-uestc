            <input type="hidden" name="id" value="{{ $programme->id }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="inputTitle">Title</label>
                <input type="text" id="inputTitle" name="title" value="{{ $programme->title }}" class="form-control" required>
            </div>
