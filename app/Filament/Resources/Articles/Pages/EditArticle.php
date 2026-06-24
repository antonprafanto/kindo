<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (auth()->user()?->isAuthor()) {
            $this->record->refresh();

            if ($this->record->status === 'published') {
                Notification::make()
                    ->title('Artikel sudah dipublikasikan')
                    ->body('Artikel yang sudah terbit tidak bisa diedit. Hubungi admin jika perlu revisi.')
                    ->warning()
                    ->send();

                $this->redirect(ArticleResource::getUrl('index'));
            }
        }
    }

    public function getTitle(): string
    {
        return 'Edit Artikel';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isAuthor()) {
            $this->assertAuthorCanMutateArticle();

            unset($data['is_featured'], $data['published_at']);

            if (($data['status'] ?? '') === 'published') {
                $data['status'] = 'pending_review';
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()?->isAuthor()) {
            return [
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn () => $this->record->fresh()->status === 'draft')
                    ->before(fn () => $this->assertAuthorCanMutateArticle(requireDraft: true)),
            ];
        }

        return [
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()->label('Hapus Permanen'),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }

    private function assertAuthorCanMutateArticle(bool $requireDraft = false): void
    {
        $this->record->refresh();

        if ($this->record->status === 'published') {
            abort(403, 'Artikel yang sudah terbit tidak bisa diubah.');
        }

        if ($requireDraft && $this->record->status !== 'draft') {
            abort(403, 'Hanya artikel draft yang bisa dihapus.');
        }
    }
}
