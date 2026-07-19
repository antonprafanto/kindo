<?php

namespace App\Filament\Resources\Comments\Tables;

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
use Illuminate\Support\Str;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('author_name')
                    ->label('Nama')
                    ->searchable()
                    ->description(fn ($record) => $record->author_email),

                TextColumn::make('body')
                    ->label('Komentar')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('article.title')
                    ->label('Artikel')
                    ->limit(40)
                    ->url(fn ($record) => $record->article
                        ? route('articles.show', $record->article->slug)
                        : null)
                    ->openUrlInNewTab(),

                TextColumn::make('parent.author_name')
                    ->label('Balasan ke')
                    ->placeholder('—')
                    ->description(fn ($record) => $record->parent
                        ? Str::limit(strip_tags($record->parent->body), 50)
                        : null)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'spam'     => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'pending'  => 'Menunggu',
                        'spam'     => 'Spam',
                        default    => $state,
                    }),

                TextColumn::make('ip_address')
                    ->label('IP')
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
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'spam'     => 'Spam',
                    ]),

                SelectFilter::make('article')
                    ->label('Artikel')
                    ->relationship('article', 'title'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'approved')
                    ->action(fn ($record) => $record->update(['status' => 'approved'])),

                Action::make('spam')
                    ->label('Spam')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== 'spam')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'spam']);

                        Notification::make()
                            ->title('Komentar ditandai spam')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Hapus')
                    ->successNotificationTitle('Komentar dihapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Setujui Semua')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each(
                            fn ($r) => $r->update(['status' => 'approved'])
                        ))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('spam')
                        ->label('Tandai Spam')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn ($r) => $r->update(['status' => 'spam']));

                            Notification::make()
                                ->title('Komentar ditandai spam')
                                ->body($records->count().' komentar diperbarui.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Hapus')
                        ->successNotificationTitle('Komentar dihapus'),
                ]),
            ]);
    }
}
