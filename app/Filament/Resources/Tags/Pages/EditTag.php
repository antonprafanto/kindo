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
            DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Hapus tag?')
                ->modalDescription(function (): string {
                    $count = $this->record->articles()->count();

                    return $count > 0
                        ? "Ada {$count} artikel yang memakai tag ini. Soft-delete menyembunyikan tag dari daftar; pivot artikel↔tag biasanya tetap sampai force-delete. Pertimbangkan lepas tag dari artikel dulu."
                        : 'Tag ini tidak punya artikel. Soft-delete bisa dipulihkan nanti.';
                }),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->modalHeading('Hapus tag secara permanen?')
                ->modalDescription(function (): string {
                    $count = $this->record->articles()->count();

                    return $count > 0
                        ? "PERMANEN: tag dan slug hilang. Relasi ke {$count} artikel akan terputus. Tidak bisa dibatalkan."
                        : 'PERMANEN: tag dan slug hilang selamanya. Soft-delete lebih aman jika masih mungkin dipulihkan.';
                }),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }
}
