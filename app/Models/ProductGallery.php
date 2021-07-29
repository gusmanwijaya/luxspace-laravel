<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    use HasFactory;

    protected $table = "product_galleries";
    protected $primaryKey = "id";
    protected $fillable = ['products_id', 'url', 'is_featured'];

    public function products()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }
}
