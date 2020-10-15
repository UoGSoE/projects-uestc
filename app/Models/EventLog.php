<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log($userId, $message)
    {
        $log = new static;
        $log->user_id = $userId;
        $log->message = $message;
        $log->save();
    }
}
