<?php

namespace Laravel\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Laravel\Observers\ProductObserver;

#[ObservedBy([ProductObserver::class])]
class Product extends Model
{
    protected $fillable = ['name', 'price'];
}
