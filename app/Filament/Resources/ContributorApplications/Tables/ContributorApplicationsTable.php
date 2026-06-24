<?php

namespace App\Filament\Resources\ContributorApplications\Tables;

use App\Models\ContributorApplication;
use App\Services\ContributorService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContributorApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('topic_expertise')
                    ->label('Keahlian')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Ditinjau')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                Action::make('viewMotivation')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ContributorApplication $record) => 'Aplikasi — ' . $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (ContributorApplication $record) => view('filament.contributor-application-detail', ['record' => $record])),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Aplikasi Kontributor?')
                    ->modalDescription('Akun penulis akan dibuat dan email reset password dikirim ke pelamar.')
                    ->visible(fn (ContributorApplication $record) => $record->status === 'pending')
                    ->action(function (ContributorApplication $record) {
                        try {
                            app(ContributorService::class)->approve($record);

                            Notification::make()
                                ->title('Kontributor disetujui')
                                ->body('Akun penulis dibuat dan email notifikasi terkirim.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menyetujui')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ContributorApplication $record) => $record->status === 'pending')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan (opsional)')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('Akan dikirim ke pelamar via email. Mereka boleh mengajukan ulang.'),
                    ])
                    ->action(function (ContributorApplication $record, array $data) {
                        try {
                            app(ContributorService::class)->reject(
                                $record,
                                $data['rejection_reason'] ?? null
                            );

                            Notification::make()
                                ->title('Aplikasi ditolak')
                                ->body('Email notifikasi terkirim ke pelamar.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menolak')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
