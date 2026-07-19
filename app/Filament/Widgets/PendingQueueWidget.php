<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Comments\CommentResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\Article;
use App\Models\Comment;
use App\Models\ContactMessage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingQueueWidget extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = -3;

    protected ?string $heading = 'Antrean kerja';

    protected ?string $description = 'Item yang menunggu tindakan admin';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $pendingArticles = Article::query()->where('status', 'pending_review')->count();
        $pendingComments = Comment::pending()->count();
        $unreadContacts = ContactMessage::unread()->count();

        return [
            Stat::make('Artikel review', $pendingArticles)
                ->description('Status menunggu review')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($pendingArticles > 0 ? 'warning' : 'success')
                ->url(ArticleResource::getUrl('index', [
                    'filters' => [
                        'status' => ['value' => 'pending_review'],
                    ],
                ])),
            Stat::make('Komentar pending', $pendingComments)
                ->description('Menunggu moderasi')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($pendingComments > 0 ? 'warning' : 'success')
                ->url(CommentResource::getUrl('index', [
                    'filters' => [
                        'status' => ['value' => 'pending'],
                    ],
                ])),
            Stat::make('Pesan unread', $unreadContacts)
                ->description('Kontak belum dibaca')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($unreadContacts > 0 ? 'warning' : 'success')
                ->url(ContactMessageResource::getUrl('index')),
        ];
    }
}
