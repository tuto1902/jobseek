<?php

namespace App\Filament\Resources\Publishers\Schemas;

use App\PublisherStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PublisherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter company name'),

                        TextInput::make('email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('contact@company.com'),

                        TextInput::make('website')
                            ->label('Website URL')
                            ->url()
                            ->nullable()
                            ->maxLength(255)
                            ->placeholder('https://company.com'),

                        FileUpload::make('logo')
                            ->label('Company Logo')
                            ->image()
                            ->directory('publisher-logos')
                            ->disk('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->nullable(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                PublisherStatus::Active->value => PublisherStatus::Active->getLabel(),
                                PublisherStatus::Inactive->value => PublisherStatus::Inactive->getLabel(),
                            ])
                            ->default(PublisherStatus::Active->value)
                            ->required(),
                    ]),
            ]);
    }
}
