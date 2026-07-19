<?php

namespace App\Filament\Resources\ContributorApplications\Pages;

use App\Filament\Resources\ContributorApplications\ContributorApplicationResource;
use App\Models\ContributorApplication;
use App\Services\ContributorService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListContributorApplications extends ListRecords
{
    protected static string $resource = ContributorApplicationResource::class;

    public function getTitle(): string
    {
        return 'Aplikasi Kontributor';
    }

    public function getSubheading(): ?string
    {
        $pending  = ContributorApplication::pending()->count();
        $approved = ContributorApplication::where('status', 'approved')->count();
        $rejected = ContributorApplication::where('status', 'rejected')->count();

        return "Menunggu: {$pending} · Disetujui: {$approved} · Ditolak: {$rejected}";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Manual')
                ->icon('heroicon-o-plus'),
        ];
    }

    #[On('contributor-send-onboarding')]
    public function sendOnboardingFromNotification(int $applicationId): void
    {
        $application = ContributorApplication::find($applicationId);

        if (! $application || $application->status !== 'approved') {
            Notification::make()
                ->title('Tidak bisa mengirim onboarding')
                ->body('Aplikasi tidak ditemukan atau belum disetujui.')
                ->danger()
                ->send();

            return;
        }

        try {
            $service = app(ContributorService::class);
            $service->sendOnboardingEmail(
                $application,
                $service->defaultOnboardingSubject(),
                null,
                true,
            );

            Notification::make()
                ->title('Email onboarding terkirim')
                ->body('Dikirim ke '.$application->email)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim email onboarding')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
