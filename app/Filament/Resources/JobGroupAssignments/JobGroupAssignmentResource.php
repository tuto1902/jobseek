<?php

namespace App\Filament\Resources\JobGroupAssignments;

use App\Filament\Resources\JobGroupAssignments\Pages\CreateJobGroupAssignment;
use App\Filament\Resources\JobGroupAssignments\Pages\EditJobGroupAssignment;
use App\Filament\Resources\JobGroupAssignments\Pages\ListJobGroupAssignments;
use App\Filament\Resources\JobGroupAssignments\Schemas\JobGroupAssignmentForm;
use App\Filament\Resources\JobGroupAssignments\Tables\JobGroupAssignmentsTable;
use App\Models\JobGroupAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobGroupAssignmentResource extends Resource
{
    protected static ?string $model = JobGroupAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return JobGroupAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobGroupAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJobGroupAssignments::route('/'),
            'create' => CreateJobGroupAssignment::route('/create'),
            'edit' => EditJobGroupAssignment::route('/{record}/edit'),
        ];
    }
}
