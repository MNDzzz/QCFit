<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'category_id',
        'source_id',
        'brand_id',
        'name',
        'external_id',
        'original_link'
    ];

    /**
     * Relación con el marketplace de origen
     */
    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Relación con la marca del producto
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Relación con la categoría del sistema
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function outfits()
    {
        return $this->belongsToMany(Outfit::class)
            ->withPivot(['pos_x', 'pos_y', 'rotation', 'scale_x', 'scale_y', 'z_index', 'is_flipped', 'selected_image_id'])
            ->withTimestamps();
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'product_user')->withTimestamps();
    }
}
