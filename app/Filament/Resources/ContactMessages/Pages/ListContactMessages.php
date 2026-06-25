<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    public function getTitle(): string
    {
        return 'Pesan Kontak';
    }

    public function getSubheading(): ?string
    {
        $unread   = ContactMessage::unread()->count();
        $read     = ContactMessage::where('status', 'read')->count();
        $archived = ContactMessage::where('status', 'archived')->count();

        return "Belum dibaca: {$unread} · Dibaca: {$read} · Diarsipkan: {$archived}";
    }
}
