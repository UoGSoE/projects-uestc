<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLink extends Model
{
    use HasFactory;

    protected $fillable = ['url'];
}
