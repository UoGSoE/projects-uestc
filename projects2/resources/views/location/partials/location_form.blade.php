        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $location->id }}">
        <div class="form-group">
            <label for="inputTitle">Title</label>
            <input type="text" id="inputTitle" name="title" value="{{ $location->title }}" class="form-control" required>
        </div>
        <div class="checkbox">
            <label>
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" name="is_default" value="1" @if ($location->is_default) checked @endif> Default?
            </label>
        </div>
