<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Filterable;

    protected $table = 'products';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => ActiveStatus::class,
        ];
    }
}
