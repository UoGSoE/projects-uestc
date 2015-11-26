        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $location->id }}">
        <div class="form-group">
            <label for="inputTitle">Title</label>
            <input type="text" id="inputTitle" name="title" value="{{ $location->title }}" class="form-control" required>
        </div>
