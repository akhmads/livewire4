<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    use Filterable;

    protected $table = 'order_details';

    protected $guarded = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }
}
