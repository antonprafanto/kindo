<?php

namespace App\Filament\Resources\ContributorApplications\Pages;

use App\Filament\Resources\ContributorApplications\ContributorApplicationResource;
use App\Support\EmailNormalizer;
use Filament\Resources\Pages\CreateRecord;

class CreateContributorApplication extends CreateRecord
{
    protected static string $resource = ContributorApplicationResource::class;

    public function getTitle(): string
    {
        return 'Tambah Aplikasi Manual';
    }

    public function getSubheading(): ?string
    {
        return 'Untuk memasukkan ulang aplikasi yang hilang dari database (misalnya dari email notifikasi admin).';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['email'] = EmailNormalizer::normalize($data['email']);
        $data['status'] = 'pending';

        return $data;
    }
}
