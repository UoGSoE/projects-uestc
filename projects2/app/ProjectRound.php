<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectRound extends Model
{
    protected $fillable = ['project_id', 'user_id', 'round', 'accepted'];
}
