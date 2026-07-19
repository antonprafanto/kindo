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
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus kategori?')
                ->modalDescription(function (): string {
                    $count = $this->record->articles()->count();

                    return $count > 0
                        ? "Ada {$count} artikel yang memakai kategori ini. Soft-delete akan menyembunyikan kategori dari navigasi; artikel tetap menyimpan category_id (bisa dipulihkan). Pertimbangkan pindahkan artikel dulu."
                        : 'Kategori ini tidak punya artikel. Soft-delete bisa dipulihkan nanti.';
                }),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->modalHeading('Hapus kategori secara permanen?')
                ->modalDescription(function (): string {
                    $count = $this->record->articles()->count();

                    return $count > 0
                        ? "PERMANEN: kategori dan slug hilang. {$count} artikel akan kehilangan referensi kategori (category_id orphan). Tidak bisa dibatalkan."
                        : 'PERMANEN: kategori dan slug hilang selamanya. Soft-delete lebih aman jika masih mungkin dipulihkan.';
                }),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }
}
