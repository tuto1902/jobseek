<?php

namespace App\Filament\Resources\JobGroups;

use App\Filament\Resources\JobGroups\Pages\CreateJobGroup;
use App\Filament\Resources\JobGroups\Pages\EditJobGroup;
use App\Filament\Resources\JobGroups\Pages\ListJobGroups;
use App\Filament\Resources\JobGroups\Schemas\JobGroupForm;
use App\Filament\Resources\JobGroups\Tables\JobGroupsTable;
use App\Models\JobGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobGroupResource extends Resource
{
    protected static ?string $model = JobGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return JobGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobGroups::route('/'),
            'create' => CreateJobGroup::route('/create'),
            'edit' => EditJobGroup::route('/{record}/edit'),
        ];
    }
}
