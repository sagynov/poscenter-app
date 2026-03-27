<?php

namespace App\Filament\Resources\TelegramUsers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TelegramUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')
                    ->label(__('telegram_user.phone'))
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label(__('telegram_user.first_name'))
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label(__('telegram_user.last_name'))
                    ->searchable(),
                TextColumn::make('username')
                    ->label(__('telegram_user.username'))
                    ->searchable(),
                TextColumn::make('city')
                    ->label(__('telegram_user.city'))
                    ->searchable(),
                TextColumn::make('shipping_address')
                    ->label(__('telegram_user.shipping_address'))
                    ->searchable(),
                TextColumn::make('telegram_id')
                    ->label(__('telegram_user.telegram_id'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('telegram_user.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('telegram_user.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
