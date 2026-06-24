<?php

namespace App\Filament\Resources\Comments;

use App\Filament\Concerns\AdminOnlyResource;
use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\Comments\Tables\CommentsTable;
use App\Models\Comment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel  = 'Komentar';
    protected static ?string $modelLabel       = 'Komentar';
    protected static ?string $pluralModelLabel = 'Komentar';
    protected static ?int    $navigationSort   = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = Comment::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
