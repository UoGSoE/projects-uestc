
{{ csrf_field() }}
<div class="form-group">
    <label for="inputTitle">Title</label>
    <input type="text" id="inputTitle" name="title" value="{{ $permission->title }}" class="form-control" required>
</div>
<div class="form-group">
    <label for="inputLabel">Label/Description</label>
    <input type="text" id="inputLabel" name="label" value="{{ $permission->label }}" class="form-control" required>
</div>