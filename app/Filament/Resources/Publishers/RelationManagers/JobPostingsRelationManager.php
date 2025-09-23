<?php

namespace App\Filament\Resources\Publishers\RelationManagers;

use App\Filament\Resources\JobPostings\Schemas\JobPostingForm;
use App\Filament\Resources\JobPostings\Tables\JobPostingsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class JobPostingsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobPostings';

    public static function getTabComponent(Model $ownerRecord, string $pageClass): Tab
    {
        return Tab::make('Job Postings')
            ->badge($ownerRecord->jobPostings()->count())
            ->badgeColor('primary')
            ->icon(Heroicon::OutlinedBriefcase);
    }

    public function form(Schema $schema): Schema
    {
        return JobPostingForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return JobPostingsTable::configure($table)
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->with(['publisher']))
            ->headerActions([
                CreateAction::make(),
            ])
            ->filters(
                collect(JobPostingsTable::configure($table)->getFilters())
                    ->reject(fn ($filter) => $filter instanceof \Filament\Tables\Filters\SelectFilter && $filter->getName() === 'publisher')
                    ->values()
                    ->toArray()
            );
    }
}
