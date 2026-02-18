<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'type',
        'source',
        'embedding'
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
