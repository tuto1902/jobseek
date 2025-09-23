<?php

namespace App\Filament\Resources\JobPostings\Schemas;

use App\EmploymentType;
use App\JobStatus;
use App\Filament\Resources\Publishers\RelationManagers\JobPostingsRelationManager;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobPostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('publisher_id')
                                    ->label('Publisher')
                                    ->relationship('publisher', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select a publisher')
                                    ->hiddenOn(JobPostingsRelationManager::class),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        JobStatus::Draft->value => JobStatus::Draft->getLabel(),
                                        JobStatus::Active->value => JobStatus::Active->getLabel(),
                                        JobStatus::Expired->value => JobStatus::Expired->getLabel(),
                                        JobStatus::Archived->value => JobStatus::Archived->getLabel(),
                                    ])
                                    ->default(JobStatus::Draft->value)
                                    ->required(),
                            ]),

                        TextInput::make('title')
                            ->label('Job Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Senior Software Engineer'),

                        RichEditor::make('description')
                            ->label('Job Description')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->placeholder('Provide a detailed job description...'),
                    ]),

                Section::make('Location & Work Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location')
                                    ->label('Location')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., New York, NY, USA'),

                                Select::make('employment_type')
                                    ->label('Employment Type')
                                    ->options([
                                        EmploymentType::FullTime->value => EmploymentType::FullTime->getLabel(),
                                        EmploymentType::PartTime->value => EmploymentType::PartTime->getLabel(),
                                        EmploymentType::Contract->value => EmploymentType::Contract->getLabel(),
                                        EmploymentType::Freelance->value => EmploymentType::Freelance->getLabel(),
                                        EmploymentType::Internship->value => EmploymentType::Internship->getLabel(),
                                        EmploymentType::Temporary->value => EmploymentType::Temporary->getLabel(),
                                    ])
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->placeholder('e.g., 40.7128')
                                    ->helperText('Optional: Precise location coordinate'),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->placeholder('e.g., -74.0060')
                                    ->helperText('Optional: Precise location coordinate'),

                                Toggle::make('remote_work_option')
                                    ->label('Remote Work Available')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Application & Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('application_url')
                                    ->label('Application URL')
                                    ->url()
                                    ->required()
                                    ->maxLength(500)
                                    ->placeholder('https://company.com/apply'),

                                DatePicker::make('expiration_date')
                                    ->label('Expiration Date')
                                    ->required()
                                    ->minDate(now())
                                    ->default(now()->addMonth()),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('category')
                                    ->label('Category/Industry')
                                    ->maxLength(255)
                                    ->placeholder('e.g., Technology, Marketing'),

                                Toggle::make('featured')
                                    ->label('Featured Job')
                                    ->default(false)
                                    ->helperText('Featured jobs are highlighted in search results'),

                                TextInput::make('rpa')
                                    ->label('Revenue Per Action (RPA)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->prefix('$')
                                    ->helperText('Optional: Cost per application/click'),
                            ]),
                    ]),
            ]);
    }
}
