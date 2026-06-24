<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        $table = $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->width(80)
                    ->height(50)
                    ->defaultImageUrl(asset('og-default.png')),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn ($record) => $record->category?->name),

                TextColumn::make('user.name')
                    ->label('Penulis')
                    ->sortable()
                    ->toggleable()
                    ->visible($isAdmin),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published'      => 'success',
                        'pending_review' => 'info',
                        'draft'          => 'warning',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_review' => 'Menunggu Review',
                        'published'      => 'Published',
                        'draft'          => 'Draft',
                        default          => $state,
                    }),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus'),

                TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->suffix(' x'),

                TextColumn::make('published_at')
                    ->label('Dipublish')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->since(),

                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(array_filter([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'          => 'Draft',
                        'pending_review' => 'Menunggu Review',
                        'published'      => 'Published',
                    ]),

                SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                $isAdmin ? TrashedFilter::make() : null,
            ]))
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $isAdmin || $record->status !== 'published'),
            ]);

        if ($isAdmin) {
            $table->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish Semua')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Publish Artikel Terpilih?')
                        ->modalDescription('Semua artikel yang dipilih akan dipublish sekarang.')
                        ->action(fn (Collection $records) => $records->each(
                            fn ($r) => $r->update([
                                'status'       => 'published',
                                'published_at' => $r->published_at ?? now(),
                            ])
                        ))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('unpublish')
                        ->label('Unpublish Semua')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Unpublish Artikel Terpilih?')
                        ->modalDescription('Semua artikel yang dipilih akan dikembalikan ke Draft.')
                        ->action(fn (Collection $records) => $records->each(
                            fn ($r) => $r->update(['status' => 'draft'])
                        ))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()->label('Hapus'),
                    ForceDeleteBulkAction::make()->label('Hapus Permanen'),
                    RestoreBulkAction::make()->label('Pulihkan'),
                ]),
            ]);
        }

        return $table->defaultSort('created_at', 'desc');
    }
}
