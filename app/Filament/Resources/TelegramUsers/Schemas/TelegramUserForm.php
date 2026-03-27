<?php

namespace App\Filament\Resources\TelegramUsers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TelegramUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('telegram_id')
                    ->label(__('telegram_user.telegram_id'))
                    ->required()
                    ->numeric(),
                TextInput::make('phone')
                    ->label(__('telegram_user.phone'))
                    ->required()
                    ->numeric(),
                TextInput::make('first_name')
                    ->label(__('telegram_user.first_name'))
                    ->required(),
                TextInput::make('last_name')->label(__('telegram_user.last_name')),
                TextInput::make('username')->label(__('telegram_user.username')),
                TextInput::make('city')->label(__('telegram_user.city')),
                TextInput::make('shipping_address')->label(__('telegram_user.shipping_address')),
            ]);
    }
}
