<?php

namespace App\Filament\Resources\JobGroups\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    public static function getTabComponent(Model $ownerRecord, string $pageClass): Tab
    {
        return Tab::make('Assignments')
            ->badge($ownerRecord->assignments()->count())
            ->badgeColor(fn () => $ownerRecord->isWeightValid() ? 'success' : 'danger')
            ->badgeTooltip(fn () => $ownerRecord->isWeightValid()
                ? 'Total weight: 100%'
                : 'Total weight: '.$ownerRecord->getTotalWeightAttribute().'%'
            )
            ->icon(Heroicon::OutlinedRectangleStack);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('Job Posting')
                            ->relationship(
                                name: 'jobPosting',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn ($query) => $query->whereNotIn('job_postings.id', $this->getOwnerRecord()->jobPostings()->pluck('job_postings.id'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a job posting'),

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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('jobPosting.title')
            ->columns([
                TextColumn::make('jobPosting.title')
                    ->label('Job Posting')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('jobPosting.publisher.name')
                    ->label('Publisher')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('weight_percentage')
                    ->label('Weight')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->alignment(Alignment::End)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('job_posting.publisher_id')
                    ->label('Publisher')
                    ->relationship('jobPosting.publisher', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
