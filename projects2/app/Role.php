<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function permissions()
    {
        return $this->BelongsToMany(Permission::class);
    }

    public function givePermissionTo($permission)
    {
        $this->permissions()->save($permission);
    }
}
