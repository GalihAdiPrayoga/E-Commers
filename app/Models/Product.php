<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'quantity', 'image'];

    protected $casts = [
        'image' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Product has many OrderDetails
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
