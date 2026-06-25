<?php

namespace App\Filament\Resources\ContributorApplications\Pages;

use App\Filament\Resources\ContributorApplications\ContributorApplicationResource;
use App\Models\ContributorApplication;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContributorApplications extends ListRecords
{
    protected static string $resource = ContributorApplicationResource::class;

    public function getTitle(): string
    {
        return 'Aplikasi Kontributor';
    }

    public function getSubheading(): ?string
    {
        $pending  = ContributorApplication::pending()->count();
        $approved = ContributorApplication::where('status', 'approved')->count();
        $rejected = ContributorApplication::where('status', 'rejected')->count();

        return "Menunggu: {$pending} · Disetujui: {$approved} · Ditolak: {$rejected}";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Manual')
                ->icon('heroicon-o-plus'),
        ];
    }
}
