<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color'
    ];

    // relasi many to many
    public function posts(): BelongsToMany {
        return $this->belongsToMany(Post::class);
    }

    // membuat slug secara otomatis
    protected static function boot(): void {
        parent::boot();

        static::creating(function($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
