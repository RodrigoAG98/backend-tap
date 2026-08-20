<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasUlids, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'profiles';

    public $incrementing = false;

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
}
