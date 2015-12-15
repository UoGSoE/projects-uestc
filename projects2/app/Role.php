<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    protected $fillable = ['title', 'label'];

    public function permissions()
    {
        return $this->BelongsToMany(Permission::class);
    }

    public function givePermissionTo($permission)
    {
        if ($this->hasPermission($permission->id)) {
            return false;
        }
        $this->permissions()->save($permission);
    }

    public function hasPermission($permission_id)
    {
        return $this->permissions->contains($permission_id);
    }
}
