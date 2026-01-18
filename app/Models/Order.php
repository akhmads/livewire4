<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use Filterable;

    protected $table = 'orders';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'total' => 'decimal:2',
            // 'is_active' => ActiveStatus::class,
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
