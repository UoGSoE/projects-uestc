
{{ csrf_field() }}
<div class="form-group">
    <label for="inputTitle">Title</label>
    <input type="text" id="inputTitle" name="title" value="{{ $role->title }}" class="form-control" required>
</div>
<div class="form-group">
    <label for="inputLabel">Label/Description</label>
    <input type="text" id="inputLabel" name="label" value="{{ $role->label }}" class="form-control" required>
</div>
@foreach ($permissions as $permission)
    <div class="checkbox">
        <label>
            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @if ($role->hasPermission($permission->id)) checked @endif> {{ $permission->label }}
        </label>
    </div>
@endforeach