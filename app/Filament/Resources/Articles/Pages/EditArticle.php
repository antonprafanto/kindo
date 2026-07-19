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

    protected ?string $statusBeforeSave = null;

    protected ?string $originalUpdatedAt = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->originalUpdatedAt = $this->record->updated_at?->toJSON();

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

    protected function afterFill(): void
    {
        $this->rememberArticleFormBaseline();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $freshUpdatedAt = $this->record->fresh()?->updated_at?->toJSON();

        if (
            $this->originalUpdatedAt !== null
            && $freshUpdatedAt !== null
            && $freshUpdatedAt !== $this->originalUpdatedAt
        ) {
            Notification::make()
                ->title('Konflik penyimpanan')
                ->body('Artikel diubah di tempat lain sejak kamu membuka halaman ini. Muat ulang, lalu simpan lagi.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        // Cover dikelola lewat tombol Upload Cover di daftar artikel, bukan form edit.
        unset($data['cover_image']);
        $data['body'] = $this->record->body;
        $this->statusBeforeSave = $this->record->status;

        if (auth()->user()?->isAuthor()) {
            $this->assertAuthorCanMutateArticle();

            unset($data['is_featured'], $data['published_at'], $data['review_notes']);

            if ($this->record->status === 'published') {
                $data['status'] = 'published';
            } elseif (($data['status'] ?? '') === 'published') {
                $data['status'] = 'pending_review';
            }

            // Resubmit for review clears previous reject notes
            if (($data['status'] ?? '') === 'pending_review') {
                $data['review_notes'] = null;
            }
        } else {
            $wasPending = $this->record->status === 'pending_review';
            $becomingDraft = ($data['status'] ?? '') === 'draft';

            if ($wasPending && $becomingDraft && blank($data['review_notes'] ?? null)) {
                Notification::make()
                    ->title('Isi catatan review saat menolak artikel')
                    ->body('Tuliskan alasan penolakan agar penulis tahu apa yang perlu diperbaiki.')
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->rememberArticleFormBaseline();
        $this->originalUpdatedAt = $this->record->fresh()?->updated_at?->toJSON();

        $article = $this->record->fresh(['user']);

        if (
            auth()->user()?->isAuthor()
            && $this->statusBeforeSave !== 'pending_review'
            && $article?->status === 'pending_review'
        ) {
            Notification::make()
                ->title('Artikel dikirim ke review')
                ->body('Tim editorial akan meninjau dalam 3–5 hari kerja. Kamu akan mendapat notifikasi setelah review selesai.')
                ->success()
                ->send();
        }

        if (
            auth()->user()?->isAdmin()
            && $this->statusBeforeSave === 'pending_review'
            && $article?->status === 'draft'
            && filled($article->review_notes)
            && $article->user?->email
        ) {
            try {
                \App\Support\MultipartMail::send('emails.article-rejected', [
                    'article'     => $article,
                    'authorName'  => $article->user->name,
                    'reviewNotes' => $article->review_notes,
                    'editUrl'     => url('/admin/articles/' . $article->id . '/edit'),
                ], function ($message) use ($article) {
                    $message->to($article->user->email)
                        ->subject('[Koding Indonesia] Artikel perlu revisi — ' . $article->title);
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }
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
                    ->visible(fn () => ! $this->record->trashed() && $this->record->fresh()->status === 'draft')
                    ->before(fn () => $this->assertAuthorCanMutateArticle(requireDraft: true)),
                RestoreAction::make()
                    ->label('Pulihkan')
                    ->visible(fn () => $this->record->trashed()),
            ];
        }

        return [
            $editBody,
            $this->makePreviewAction(),
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()
                ->label('Hapus Permanen')
                ->modalHeading('Hapus artikel secara permanen?')
                ->modalDescription('Tindakan ini tidak bisa dibatalkan. Artikel, cover, dan slug akan hilang permanen — slug bisa dipakai ulang oleh artikel lain. Soft-delete (Hapus) lebih aman jika masih mungkin dipulihkan.'),
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
