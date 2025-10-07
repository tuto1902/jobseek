<?php

namespace App\Filament\Resources\JobGroups\Schemas;

use App\GroupStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class JobGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Group Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter group name'),

                        Textarea::make('description')
                            ->label('Description')
                            ->nullable()
                            ->rows(3)
                            ->placeholder('Enter group description'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                GroupStatus::Draft->value => GroupStatus::Draft->getLabel(),
                                GroupStatus::Active->value => GroupStatus::Active->getLabel(),
                                GroupStatus::Inactive->value => GroupStatus::Inactive->getLabel(),
                            ])
                            ->default(GroupStatus::Draft->value)
                            ->required(),
                    ]),
            ]);
    }
}
