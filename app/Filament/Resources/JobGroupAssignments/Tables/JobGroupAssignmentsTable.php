<?php

namespace App\Filament\Resources\JobGroupAssignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobGroupAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                TextColumn::make('jobGroup.name')
                    ->label('Job Group')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('weight_percentage')
                    ->label('Weight')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color('success'),

                TextColumn::make('jobGroup.total_weight')
                    ->label('Group Total')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color(fn ($state): string => abs($state - 100.0) < 0.01 ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->alignment(Alignment::End)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('job_group_id')
                    ->label('Job Group')
                    ->relationship('jobGroup', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('job_posting.publisher_id')
                    ->label('Publisher')
                    ->relationship('jobPosting.publisher', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
