<?php

namespace App\Filament\Resources\WebRoles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WebRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                Hidden::make('guard_name')
                    ->default('web'),
                CheckboxList::make('permissions')
                    ->relationship(
                        name: 'permissions',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) =>
                        $query->where('guard_name', 'web')
                    )
                    ->columns(2),
            ]);
    }
}
