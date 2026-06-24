<?php

namespace App\Filament\Resources\Comments\Pages;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    public function getTitle(): string
    {
        return 'Moderasi Komentar';
    }

    public function getSubheading(): ?string
    {
        $pending  = Comment::pending()->count();
        $approved = Comment::where('status', 'approved')->count();
        $spam     = Comment::where('status', 'spam')->count();

        return "Menunggu: {$pending} · Disetujui: {$approved} · Spam: {$spam}";
    }
}
