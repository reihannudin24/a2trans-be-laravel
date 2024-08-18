<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Categories::class, 'categories_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }


    public function facilities()
    {
        return $this->belongsToMany(Facilities::class, 'pivot_bus_facilities', 'bus_id', 'facilities_id');
    }

    public function images()
    {
        return $this->hasMany(ImageBus::class, 'bus_id');
    }

}
