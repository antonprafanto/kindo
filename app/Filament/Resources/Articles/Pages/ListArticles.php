<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

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

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        if (! auth()->user()?->isAuthor()) {
            return $table;
        }

        return $table
            ->emptyStateHeading('Belum ada artikel')
            ->emptyStateDescription('Mulai tulis artikel pertamamu. Baca panduan kontributor jika masih ragu soal format, kategori, atau alur review.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Tulis Artikel Baru')
                    ->icon('heroicon-o-plus'),
                Action::make('panduan')
                    ->label('Baca Panduan')
                    ->icon('heroicon-o-book-open')
                    ->url(url('/menjadi-kontributor'))
                    ->openUrlInNewTab()
                    ->color('gray'),
            ]);
    }
}
