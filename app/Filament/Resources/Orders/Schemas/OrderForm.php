<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('order.name'))
                    ->required(),
                TextInput::make('phone')
                    ->label(__('order.phone'))
                    ->required(),
                Repeater::make('items')
                  ->label(__('order.items'))
                  ->schema([
                      TextInput::make('name')
                          ->label('Название')
                          ->required(),
                      TextInput::make('price')
                          ->label('Цена')
                          ->numeric()
                          ->required(),
                      TextInput::make('quantity')
                          ->label('Количество')
                          ->numeric()
                          ->default(1)
                          ->required(),
                      TextInput::make('subtotal')
                          ->label('Сумма')
                          ->numeric()
                          ->required(),
                  ])
                  ->columns(4)
                  ->columnSpanFull()
                  ->reorderable(false),
                TextInput::make('total')
                    ->label(__('order.total'))
                    ->required()
                    ->numeric(),
                Select::make('status')
                  ->label(__('order.status'))
                  ->options(
                      collect(OrderStatus::cases())
                          ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                  )
                  ->required(),
                TextInput::make('city')->label(__('order.city')),
                TextInput::make('shipping_address')->label(__('order.shipping_address')),
                TextInput::make('note')->label(__('order.note')),
                TextInput::make('payment_method')
                    ->label(__('order.payment_method'))
                    ->required(),
            ]);
    }
}
