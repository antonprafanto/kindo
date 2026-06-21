<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    public function getTitle(): string { return 'Edit Tag'; }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()->label('Hapus Permanen'),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }
}
