<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'source_id',
        'marketplace',
        'brand',
        'original_link'
    ];

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
