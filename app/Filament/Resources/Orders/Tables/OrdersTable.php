<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('telegram_user.first_name')
                    ->label(__('order.telegram_user_id'))
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('order.phone'))
                    ->searchable(),
                ViewColumn::make('items')
                  ->label(__('order.items'))
                  ->view('filament.tables.columns.order-items'),
                TextColumn::make('total')
                    ->label(__('order.total'))
                    ->money('KZT', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('order.status'))
                    ->badge()
                    ->color(fn(OrderStatus $state) => match($state) {
                        OrderStatus::Pending   => 'warning',
                        OrderStatus::Paid      => 'info',
                        OrderStatus::Shipped   => 'primary',
                        OrderStatus::Done      => 'success',
                        OrderStatus::Cancelled => 'danger',
                    })
                    ->formatStateUsing(fn(OrderStatus $state) => $state->label())
                    ->sortable(),
                TextColumn::make('city')
                    ->label(__('order.city'))
                    ->searchable(),
                TextColumn::make('shipping_address')
                    ->label(__('order.shipping_address'))
                    ->searchable(),
                TextColumn::make('note')
                    ->label(__('order.note'))
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('order.payment_method'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('order.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('order.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('telegram_user'))
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
