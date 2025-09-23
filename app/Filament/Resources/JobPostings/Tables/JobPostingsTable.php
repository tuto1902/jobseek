<?php

namespace App\Filament\Resources\JobPostings\Tables;

use App\EmploymentType;
use App\JobStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobPostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Job Title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->publisher->name),

                TextColumn::make('publisher.name')
                    ->label('Publisher')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (EmploymentType $state): string => $state->getColor())
                    ->formatStateUsing(fn (EmploymentType $state): string => $state->getLabel()),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (JobStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (JobStatus $state): string => $state->getLabel()),

                IconColumn::make('featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                IconColumn::make('remote_work_option')
                    ->label('Remote')
                    ->boolean()
                    ->trueIcon('heroicon-o-home')
                    ->falseIcon('heroicon-o-building-office')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('expiration_date')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->expiration_date->isPast() ? 'danger' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rpa')
                    ->label('RPA')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        JobStatus::Draft->value => JobStatus::Draft->getLabel(),
                        JobStatus::Active->value => JobStatus::Active->getLabel(),
                        JobStatus::Expired->value => JobStatus::Expired->getLabel(),
                        JobStatus::Archived->value => JobStatus::Archived->getLabel(),
                    ]),

                SelectFilter::make('employment_type')
                    ->label('Employment Type')
                    ->options([
                        EmploymentType::FullTime->value => EmploymentType::FullTime->getLabel(),
                        EmploymentType::PartTime->value => EmploymentType::PartTime->getLabel(),
                        EmploymentType::Contract->value => EmploymentType::Contract->getLabel(),
                        EmploymentType::Freelance->value => EmploymentType::Freelance->getLabel(),
                        EmploymentType::Internship->value => EmploymentType::Internship->getLabel(),
                        EmploymentType::Temporary->value => EmploymentType::Temporary->getLabel(),
                    ]),

                SelectFilter::make('publisher')
                    ->relationship('publisher', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('featured')
                    ->query(fn (Builder $query): Builder => $query->where('featured', true))
                    ->label('Featured Jobs Only'),

                Filter::make('remote')
                    ->query(fn (Builder $query): Builder => $query->where('remote_work_option', true))
                    ->label('Remote Jobs Only'),

                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expiration_date', '<', now()))
                    ->label('Expired Jobs'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
