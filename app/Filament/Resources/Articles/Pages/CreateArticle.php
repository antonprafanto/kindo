<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Tulis Artikel Baru';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        if (auth()->user()?->isAuthor()) {
            $data['is_featured'] = false;

            if (($data['status'] ?? 'draft') === 'published') {
                $data['status'] = 'draft';
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.articles.isi', ['article' => $this->record]);
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Metadata tersimpan')
            ->body('Lanjut tulis isi artikel di editor. Simpan isi, lalu kembali ke metadata bila perlu.')
            ->success()
            ->send();
    }
}
