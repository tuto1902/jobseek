<?php

namespace App\Filament\Resources\JobGroups\RelationManagers;

use Closure;
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
use Filament\Schemas\Components\View;
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
                View::make('filament.job-groups.progress-bar'),
                Grid::make(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('Job Posting')
                            ->relationship(
                                name: 'jobPosting',
                                titleAttribute: 'title',
                                modifyQueryUsing: function ($query, ?Model $record) {
                                    // Get already assigned job posting IDs
                                    $assignedIds = $this->getOwnerRecord()->jobPostings()->pluck('job_postings.id');

                                    // If editing, exclude the current record's job posting from the exclusion list
                                    if ($record && $record->job_posting_id) {
                                        $assignedIds = $assignedIds->reject(fn ($id) => $id === $record->job_posting_id);
                                    }

                                    return $query->whereNotIn('job_postings.id', $assignedIds);
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a job posting'),

                        TextInput::make('weight_percentage')
                            ->label('Weight Percentage')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->rules([
                                'numeric',
                                'min:0.01',
                                'max:100',
                                fn ($component, $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                    $jobGroup = $this->getOwnerRecord();
                                    $currentRecordId = $record?->id;

                                    // Calculate total weight excluding current record if editing
                                    $currentTotal = $jobGroup->assignments()
                                        ->when($currentRecordId, fn ($query) => $query->where('id', '!=', $currentRecordId))
                                        ->sum('weight_percentage');

                                    $newTotal = $currentTotal + (float) $value;

                                    if ($newTotal > 100.01) { // Allow small floating point tolerance
                                        $available = 100 - $currentTotal;
                                        $fail("The total weight cannot exceed 100%. Currently {$currentTotal}% is assigned. Maximum available: {$available}%");
                                    }
                                },
                            ])
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
