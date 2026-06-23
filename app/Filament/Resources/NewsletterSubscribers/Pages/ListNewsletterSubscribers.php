<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getTitle(): string
    {
        return 'Subscriber Newsletter';
    }

    public function getSubheading(): ?string
    {
        $active  = NewsletterSubscriber::active()->count();
        $pending = NewsletterSubscriber::pending()->count();

        return "Aktif: {$active} · Menunggu konfirmasi: {$pending}";
    }
}
