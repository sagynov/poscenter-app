<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                  ->required()
                  ->label(__('product.category_id'))
                  ->options(Category::pluck('name', 'id'))
                  ->searchable()
                  ->placeholder(__('product.category_id')),
                TextInput::make('name')
                    ->label(__('product.name'))
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn (Set $set, ?string $state) =>
                        $set('slug', Str::slug($state))
                    ),
                TextInput::make('slug')
                    ->label(__('product.slug'))
                    ->required()
                    ->unique(
                        table: 'products',
                        column: 'slug',
                        ignorable: fn ($record) => $record,
                    ),
                Textarea::make('description')
                    ->label(__('product.description'))
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label(__('product.price'))
                    ->required()
                    ->numeric()
                    ->prefix('₸'),
                TextInput::make('old_price')
                    ->label(__('product.old_price'))
                    ->numeric()
                    ->prefix('₸'),
                TextInput::make('stock')
                    ->label(__('product.stock'))
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('images')
                    ->label(__('product.images'))
                    ->image()
                    ->multiple()
                    ->panelLayout('grid')
                    ->reorderable()
                    ->appendFiles()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('products'),
                Toggle::make('is_active')
                    ->label(__('product.is_active'))
                    ->required()
                    ->default(true),
                TextInput::make('sort_order')
                    ->label(__('product.sort_order'))
                    ->required()
                    ->numeric()
                    ->default(100),
            ]);
    }
}
