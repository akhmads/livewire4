<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'status' => OrderStatus::class,
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->withDefault();
    }
}
