<?php

namespace App\Filament\Resources\JobGroups\Tables;

use App\GroupStatus;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class JobGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Group Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('job_count')
                    ->label('Jobs')
                    ->numeric()
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_weight')
                    ->label('Total Weight')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color(fn ($state): string => abs($state - 100.0) < 0.01 ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (GroupStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (GroupStatus $state): string => $state->getLabel())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->alignment(Alignment::End)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->alignment(Alignment::End)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        GroupStatus::Draft->value => GroupStatus::Draft->getLabel(),
                        GroupStatus::Active->value => GroupStatus::Active->getLabel(),
                        GroupStatus::Inactive->value => GroupStatus::Inactive->getLabel(),
                    ])
                    ->default(GroupStatus::Active->value),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => GroupStatus::Active]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['status' => GroupStatus::Inactive]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
