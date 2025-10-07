<?php

namespace App\Filament\Resources\JobGroupAssignments\Pages;

use App\Filament\Resources\JobGroupAssignments\JobGroupAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJobGroupAssignments extends ListRecords
{
    protected static string $resource = JobGroupAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
