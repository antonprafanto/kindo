<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $stale   = NewsletterSubscriber::pendingOlderThan(30)->count();

        $line = "Aktif: {$active} · Menunggu email: {$pending}";

        if ($stale > 0) {
            $line .= " · Pending >30 hari: {$stale}";
        }

        return $line.' — “Menunggu email” = daftar tapi belum klik link di inbox/spam.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('purgeStalePending')
                ->label('Bersihkan menunggu lama')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn (): bool => NewsletterSubscriber::pendingOlderThan(30)->exists())
                ->requiresConfirmation()
                ->modalHeading('Hapus yang menunggu lebih dari 30 hari?')
                ->modalDescription(fn (): string => 'Ada '.NewsletterSubscriber::pendingOlderThan(30)->count()
                    .' email yang daftar tapi tidak pernah mengonfirmasi selama >30 hari. Mereka belum pernah dikirimi newsletter. Hapus agar daftar tetap rapi — orangnya bisa daftar ulang kapan saja.')
                ->modalSubmitActionLabel('Ya, bersihkan')
                ->action(function (): void {
                    $deleted = app(NewsletterService::class)->purgeStalePending(30);

                    Notification::make()
                        ->title($deleted > 0 ? 'Daftar dibersihkan' : 'Tidak ada yang dihapus')
                        ->body($deleted > 0
                            ? "{$deleted} email menunggu lama sudah dihapus."
                            : 'Tidak ada pending >30 hari.')
                        ->success()
                        ->send();
                }),
            Action::make('downloadCsv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $filename = 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'status', 'confirmed_at', 'created_at']);

            NewsletterSubscriber::query()
                ->orderBy('id')
                ->chunkById(200, function ($subscribers) use ($handle) {
                    foreach ($subscribers as $subscriber) {
                        fputcsv($handle, [
                            $subscriber->email,
                            $subscriber->status,
                            $subscriber->confirmed_at?->format('Y-m-d H:i:s'),
                            $subscriber->created_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
