<?php

namespace App\Filament\Resources\Articles\Concerns;

use Filament\Actions\Action;

trait HasArticlePreviewAction
{
    protected function makePreviewAction(): Action
    {
        return Action::make('preview')
            ->label('Lihat Pratinjau')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->url(fn () => $this->record->previewUrl())
            ->openUrlInNewTab()
            ->visible(fn () => $this->record->isPreviewable())
            ->disabled(fn () => blank($this->record->previewUrl()))
            ->tooltip('Buka tampilan artikel seperti di website. Simpan perubahan dulu agar pratinjau menampilkan versi terbaru.');
    }
}
