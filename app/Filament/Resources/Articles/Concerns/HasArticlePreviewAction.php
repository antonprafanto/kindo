<?php

namespace App\Filament\Resources\Articles\Concerns;

use Filament\Actions\Action;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;

trait HasArticlePreviewAction
{
    #[Locked]
    public ?string $articleFormBaselineHash = null;

    protected function makePreviewAction(): Action
    {
        return Action::make('preview')
            ->label('Lihat Pratinjau')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->visible(fn () => $this->record->isPreviewable())
            ->disabled(fn () => blank($this->record->previewUrl()))
            // Must use action() (not url()) so requiresConfirmation can run — Filament
            // disables the Livewire click handler when a URL is set.
            ->requiresConfirmation(fn (): bool => $this->hasDirtyArticleForm())
            ->modalHeading('Perubahan belum disimpan')
            ->modalDescription('Ada perubahan di form yang belum disimpan. Pratinjau menampilkan versi tersimpan terakhir. Lanjut buka pratinjau?')
            ->modalSubmitActionLabel('Buka Pratinjau')
            ->action(function (): void {
                $url = $this->record->previewUrl();

                if (blank($url)) {
                    return;
                }

                $this->js('window.open('.Js::from($url).', "_blank", "noopener,noreferrer")');
            })
            ->tooltip('Buka tampilan artikel seperti di website. Simpan perubahan dulu agar pratinjau menampilkan versi terbaru.');
    }

    protected function rememberArticleFormBaseline(): void
    {
        if (! property_exists($this, 'data') || ! is_array($this->data ?? null)) {
            return;
        }

        $this->articleFormBaselineHash = $this->hashArticleFormData($this->data);
    }

    protected function hasDirtyArticleForm(): bool
    {
        if ($this->articleFormBaselineHash === null) {
            return false;
        }

        if (! property_exists($this, 'data') || ! is_array($this->data ?? null)) {
            return false;
        }

        return $this->hashArticleFormData($this->data) !== $this->articleFormBaselineHash;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hashArticleFormData(array $data): string
    {
        ksort($data);

        return md5((string) Str::of(json_encode($data, JSON_UNESCAPED_UNICODE))->replace('\\', ''));
    }
}
