<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;

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

}
