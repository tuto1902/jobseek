<?php

namespace App\Filament\Resources\JobGroupAssignments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class JobGroupAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('Job Posting')
                            ->relationship('jobPosting', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a job posting'),

                        Select::make('job_group_id')
                            ->label('Job Group')
                            ->relationship('jobGroup', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a job group'),

                        TextInput::make('weight_percentage')
                            ->label('Weight Percentage')
                            ->numeric()
                            ->required()
                            ->rules(['numeric', 'min:0.01', 'max:100'])
                            ->step(0.01)
                            ->placeholder('Enter weight percentage (0.01 - 100)')
                            ->suffix('%')
                            ->helperText('Weight must be between 0.01% and 100%. Total weights in a group must equal 100%.'),
                    ]),
            ]);
    }
}
