<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string { return 'Edit Kategori'; }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()->label('Hapus Permanen'),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }
}
