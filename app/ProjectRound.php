<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRound extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'user_id', 'round', 'accepted'];
}
