<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasUlids, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'product_code',
        'name',
        'brand',
        'price',
    ];

}
