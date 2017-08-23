            <input type="hidden" name="id" value="{{ $discipline->id }}">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="inputTitle">Title</label>
                <input type="text" id="inputTitle" name="title" value="{{ $discipline->title }}" class="form-control" required>
            </div>
