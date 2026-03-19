<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'items',
        'total',
        'status',
        'city',
        'shipping_address',
        'note',
        'payment_method',
    ];

    protected function casts(): array
    { 
        return [
            'items' => 'array',
            'status' => OrderStatus::class
        ];
    }

    public function telegram_user(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class);
    }
}
