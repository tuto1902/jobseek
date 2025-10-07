<?php

namespace App\Filament\Resources\JobGroups\Pages;

use App\Filament\Resources\JobGroups\JobGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJobGroup extends EditRecord
{
    protected static string $resource = JobGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
