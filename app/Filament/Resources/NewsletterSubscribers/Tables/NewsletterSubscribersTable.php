<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'       => 'success',
                        'pending'      => 'warning',
                        'unsubscribed' => 'gray',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active'       => 'Aktif',
                        'pending'      => 'Menunggu email',
                        'unsubscribed' => 'Berhenti',
                        default        => $state,
                    })
                    ->description(fn (NewsletterSubscriber $record): ?string => $record->status === 'pending'
                        ? 'Belum klik link konfirmasi'
                        : null),
                TextColumn::make('confirmed_at')
                    ->label('Dikonfirmasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
                TextColumn::make('unsubscribed_at')
                    ->label('Berhenti')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->description(fn (NewsletterSubscriber $record): ?string => $record->status === 'pending'
                        ? 'Menunggu '.$record->created_at->diffForHumans(null, true)
                        : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'      => 'Menunggu email (belum konfirmasi)',
                        'active'       => 'Aktif (siap dikirimi)',
                        'unsubscribed' => 'Berhenti',
                    ]),
            ])
            ->recordActions([
                Action::make('resendConfirmation')
                    ->label('Kirim ulang email')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->visible(fn (NewsletterSubscriber $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim ulang email konfirmasi?')
                    ->modalDescription(fn (NewsletterSubscriber $record): string => 'Kami kirim lagi link konfirmasi ke '.$record->email.'. Minta orangnya cek inbox + folder spam.')
                    ->modalSubmitActionLabel('Kirim sekarang')
                    ->action(function (NewsletterSubscriber $record): void {
                        try {
                            $ok = app(NewsletterService::class)->resendConfirmation($record);

                            if (! $ok) {
                                Notification::make()
                                    ->title('Tidak bisa dikirim')
                                    ->body('Hanya status “Menunggu email” yang bisa dikirimi ulang.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Email konfirmasi terkirim')
                                ->body('Sudah dikirim ke '.$record->email.'. Minta cek inbox atau spam.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title('Gagal mengirim email')
                                ->body('Coba lagi nanti. Jika berulang, cek konfigurasi mail production.')
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('resendConfirmation')
                        ->label('Kirim ulang konfirmasi')
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim ulang email konfirmasi?')
                        ->modalDescription('Hanya baris berstatus “Menunggu email” yang dikirimi. Minta mereka cek inbox + spam.')
                        ->modalSubmitActionLabel('Kirim sekarang')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $service = app(NewsletterService::class);
                            $sent = 0;
                            $skipped = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    $skipped++;

                                    continue;
                                }

                                try {
                                    if ($service->resendConfirmation($record)) {
                                        $sent++;
                                    } else {
                                        $skipped++;
                                    }
                                } catch (\Throwable $e) {
                                    report($e);
                                    $failed++;
                                }
                            }

                            Notification::make()
                                ->title('Kirim ulang selesai')
                                ->body("Terkirim: {$sent} · Dilewati: {$skipped} · Gagal: {$failed}")
                                ->color($failed > 0 ? 'warning' : 'success')
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label('Hapus'),
                ]),
            ]);
    }
}
