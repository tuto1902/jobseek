<?php

namespace App\Filament\Resources\JobGroupAssignments\Pages;

use App\Filament\Resources\JobGroupAssignments\JobGroupAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJobGroupAssignment extends EditRecord
{
    protected static string $resource = JobGroupAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
