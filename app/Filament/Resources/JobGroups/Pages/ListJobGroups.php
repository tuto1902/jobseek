<?php

namespace App\Filament\Resources\JobGroups\Pages;

use App\Filament\Resources\JobGroups\JobGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJobGroups extends ListRecords
{
    protected static string $resource = JobGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
