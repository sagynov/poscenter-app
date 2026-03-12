<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('category.name'))
                    ->required()
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn (Set $set, ?string $state) =>
                        $set('slug', Str::slug($state))
                    ),
                TextInput::make('slug')
                    ->label(__('category.slug'))
                    ->required()
                    ->unique(
                        table: 'categories',
                        column: 'slug',
                        ignorable: fn ($record) => $record,
                    ),
                FileUpload::make('image')
                    ->label(__('category.image'))
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('categories'),
                Select::make('parent_id')
                  ->label(__('category.parent_id'))
                  ->options(Category::whereNull('parent_id')->pluck('name', 'id'))
                  ->searchable()
                  ->nullable()
                  ->placeholder(__('category.parent')),
                TextInput::make('sort_order')
                    ->label(__('category.sort_order'))
                    ->required()
                    ->numeric()
                    ->default(100),
            ]);
    }
}
