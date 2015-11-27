<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['title', 'label'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
