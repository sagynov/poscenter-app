<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Shipped   = 'shipped';
    case Done      = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Ожидает',
            self::Paid      => 'Оплачен',
            self::Shipped   => 'Отправлен',
            self::Done      => 'Выполнен',
            self::Cancelled => 'Отменён',
        };
    }
}