<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    public function getTitle(): string
    {
        return 'Daftar Artikel';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tulis Artikel Baru'),
        ];
    }
}
