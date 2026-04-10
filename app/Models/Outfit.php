<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'thumbnail_url',
        'is_public'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['pos_x', 'pos_y', 'rotation', 'scale_x', 'scale_y', 'z_index', 'is_flipped', 'selected_image_id'])
            ->withTimestamps();
    }
}
