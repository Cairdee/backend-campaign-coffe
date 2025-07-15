<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category_id', 'price', 'image', 'description', 'rating', 'review_count', 'sugar', 'temperature', 'stock'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
