<?php

namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $fillable = ['user_id', 'token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasExpired()
    {
        return $this->created_at->lt(Carbon::now()->subMonths(3));
    }
}
