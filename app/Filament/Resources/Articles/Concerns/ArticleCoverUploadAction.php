<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

/**
 * Upload cover tanpa memuat ulang seluruh body RichEditor (hindari Livewire timeout di artikel panjang).
 */
class ArticleCoverUploadAction
{
    public static function make(): Action
    {
        return Action::make('uploadCover')
            ->label('Upload Cover')
            ->icon('heroicon-o-photo')
            ->color('success')
            ->modalHeading('Upload Gambar Sampul')
            ->modalDescription('Ideal 1200×630px (16:9), maks. 4 MB. Server otomatis konversi ke WebP.')
            ->modalSubmitActionLabel('Simpan Cover')
            ->fillForm(fn (Article $record): array => [
                'cover_image' => $record->cover_image,
            ])
            ->schema([
                FileUpload::make('cover_image')
                    ->label('Gambar cover')
                    ->disk('public')
                    ->directory('articles/covers')
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth(1200)
                    ->imageResizeTargetHeight(630)
                    ->maxSize(4096)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->required(),
            ])
            ->action(function (Article $record, array $data): void {
                $path = $data['cover_image'] ?? null;

                if (is_array($path)) {
                    $path = $path[array_key_first($path)] ?? null;
                }

                if (! is_string($path) || $path === '') {
                    Notification::make()
                        ->title('Upload gagal')
                        ->body('File cover tidak valid. Coba lagi dengan JPG, PNG, atau WebP.')
                        ->danger()
                        ->send();

                    return;
                }

                $record->update(['cover_image' => $path]);

                Notification::make()
                    ->title('Cover tersimpan')
                    ->body('Gambar sampul artikel berhasil diupload.')
                    ->success()
                    ->send();
            })
            ->visible(function (Article $record): bool {
                $user = auth()->user();

                if ($user?->isAdmin()) {
                    return true;
                }

                return (bool) ($user?->isAuthor() && $record->isOwnedBy($user));
            });
    }
}
