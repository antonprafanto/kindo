<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

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
}
