<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = ["product_name", "product_image", "product_state_id"];

    /**
     * Get the state that owns the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'product_state_id', 'id');
    }

    /**
     * Get all of the inventory for the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'product_id', 'id');
    }
}
