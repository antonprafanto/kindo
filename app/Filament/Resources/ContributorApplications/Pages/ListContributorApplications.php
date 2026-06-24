<?php

namespace App\Filament\Resources\ContributorApplications\Pages;

use App\Filament\Resources\ContributorApplications\ContributorApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListContributorApplications extends ListRecords
{
    protected static string $resource = ContributorApplicationResource::class;

    public function getTitle(): string
    {
        return 'Aplikasi Kontributor';
    }
}
