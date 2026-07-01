<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Articles\Concerns\HasArticlePreviewAction;
use App\Filament\Resources\Articles\Schemas\ArticleForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditArticle extends EditRecord
{
    use HasArticlePreviewAction;

    protected static string $resource = ArticleResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (session()->pull('body_saved')) {
            Notification::make()
                ->title('Isi artikel berhasil disimpan')
                ->success()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return 'Edit Artikel';
    }

    public function form(Schema $schema): Schema
    {
        $isAuthor = auth()->user()?->isAuthor() ?? false;
        $authorLockedStatus = $isAuthor && $this->record->status === 'published'
            ? 'published'
            : null;

        return ArticleForm::configure(
            $schema,
            includeCoverSection: false,
            excludeBodyFromForm: true,
            bodyEditorUrl: route('filament.admin.articles.isi', ['article' => $this->record]),
            authorLockedStatus: $authorLockedStatus,
        );
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['body']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Cover dikelola lewat tombol Upload Cover di daftar artikel, bukan form edit.
        unset($data['cover_image']);
        $data['body'] = $this->record->body;

        if (auth()->user()?->isAuthor()) {
            $this->assertAuthorCanMutateArticle();

            unset($data['is_featured'], $data['published_at']);

            if ($this->record->status === 'published') {
                $data['status'] = 'published';
            } elseif (($data['status'] ?? '') === 'published') {
                $data['status'] = 'pending_review';
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        $editBody = Action::make('editBody')
            ->label('Edit Isi Artikel')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->url(fn () => route('filament.admin.articles.isi', ['article' => $this->record]));

        if (auth()->user()?->isAuthor()) {
            return [
                $editBody,
                $this->makePreviewAction(),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn () => $this->record->fresh()->status === 'draft')
                    ->before(fn () => $this->assertAuthorCanMutateArticle(requireDraft: true)),
            ];
        }

        return [
            $editBody,
            $this->makePreviewAction(),
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()->label('Hapus Permanen'),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }

    private function assertAuthorCanMutateArticle(bool $requireDraft = false): void
    {
        $this->record->refresh();

        if ($requireDraft && $this->record->status !== 'draft') {
            abort(403, 'Hanya artikel draft yang bisa dihapus.');
        }
    }
}
