<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use App\Models\ContributorApplication;
use App\Support\EmailNormalizer;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ContactMessage $record) => $record->email),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),

                IconColumn::make('is_contributor_inquiry')
                    ->label('Kontributor')
                    ->boolean()
                    ->trueIcon('heroicon-o-user-plus')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->tooltip(fn (ContactMessage $record) => $record->is_contributor_inquiry
                        ? 'Pesan terkait kontributor — cek juga Aplikasi Kontributor'
                        : null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unread'   => 'warning',
                        'read'     => 'success',
                        'archived' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unread'   => 'Belum dibaca',
                        'read'     => 'Dibaca',
                        'archived' => 'Diarsipkan',
                        default    => $state,
                    }),

                TextColumn::make('auto_reply_sent_at')
                    ->label('Auto-reply')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unread'   => 'Belum dibaca',
                        'read'     => 'Dibaca',
                        'archived' => 'Diarsipkan',
                    ]),

                TernaryFilter::make('is_contributor_inquiry')
                    ->label('Terkait kontributor')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->recordActions([
                Action::make('viewMessage')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (ContactMessage $record) => 'Pesan — ' . $record->name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->mountUsing(function (ContactMessage $record) {
                        if ($record->status === 'unread') {
                            $record->update(['status' => 'read']);
                        }
                    })
                    ->modalContent(fn (ContactMessage $record) => view('filament.contact-message-detail', [
                        'record'      => $record,
                        'application' => ContributorApplication::where(
                            'email',
                            EmailNormalizer::normalize($record->email),
                        )
                            ->orderByDesc('created_at')
                            ->first(),
                    ])),

                Action::make('markRead')
                    ->label('Tandai dibaca')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ContactMessage $record) => $record->status === 'unread')
                    ->action(fn (ContactMessage $record) => $record->update(['status' => 'read'])),

                Action::make('archive')
                    ->label('Arsipkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (ContactMessage $record) => $record->status !== 'archived')
                    ->requiresConfirmation()
                    ->action(fn (ContactMessage $record) => $record->update(['status' => 'archived'])),
            ]);
    }
}
