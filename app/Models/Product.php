<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use MongoDB\Laravel\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    protected $fillable = [
        'product_code',
        'name',
        'brand',
        'price',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->id)) {
                $product->id = (string) str()->ulid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:d/m/Y H:i',
        ];
    }

    //Función necesaria para guardar automáticamente los cambios sobre el modelo
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty();
    }
}
