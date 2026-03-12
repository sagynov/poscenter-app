<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('product.name'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('product.category_id'))
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('product.price'))
                    ->money('KZT', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('old_price')
                    ->label(__('product.old_price'))
                    ->money('KZT', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('stock')
                    ->label(__('product.stock'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('product.is_active'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('product.sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('product.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('product.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('category'))
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
