<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use MongoDB\Laravel\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes, HasRoles, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'profiles';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    //Columnas que se pueden asignar de forma masiva
    protected $fillable = [
        'profile_code',
        'name',
        'sections',
    ];

    protected function casts(): array
    {
        //Al consultar desde el modelo se castea la data
        return [
            'sections'=> 'array',
            'created_at' => 'datetime:d/m/Y H:i',
        ];
    }

    protected static function booted(): void
    {
        //Se agrega id en caso de que se encuentre vacio al momento de crear
        static::creating(function (Profile $profile) {
            if (empty($profile->id)) {
                $profile->id = (string) str()->ulid();
            }
        });
    }

    //Función necesaria para guardar automáticamente los cambios sobre el modelo
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
