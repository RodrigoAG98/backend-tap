<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class Profile extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'profiles';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    protected $fillable = [
        'profile_code',
        'name',
        'sections',
    ];

    protected function casts(): array
    {
        return [
            'sections'=> 'array'
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Profile $profile) {
            if (empty($profile->id)) {
                $profile->id = (string) str()->ulid();
            }
        });
    }
}
