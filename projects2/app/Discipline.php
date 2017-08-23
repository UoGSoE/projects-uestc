<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    protected $fillable = ['title'];

    public function cssTitle()
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '-', $this->title));
    }
}
