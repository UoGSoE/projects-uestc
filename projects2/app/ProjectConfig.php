<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectConfig extends Model
{
    public static function setOption($key, $value)
    {
        $entry = static::where('key', '=', $key)->first();
        if (!$entry) {
            $entry = new static;
        }
        $entry->key = $key;
        $entry->value = $value;
        $entry->save();
    }

    public static function getOption($key, $default = null)
    {
        $entry = static::where('key', '=', $key)->first();
        if (!$entry) {
            if (!$default) {
                throw new \InvalidArgumentException("No config value for {$key}", 1);
            }
            return $default;
        }
        return $entry->value;
    }
}
