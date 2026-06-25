<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Concerns\AdminOnlyResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $navigationLabel  = 'Pesan Kontak';
    protected static ?string $modelLabel       = 'Pesan Kontak';
    protected static ?string $pluralModelLabel  = 'Pesan Kontak';
    protected static ?int    $navigationSort   = 7;

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
