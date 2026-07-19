<?php

namespace App\Filament\Resources\ContributorApplications;

use App\Filament\Concerns\AdminOnlyResource;
use App\Filament\Resources\ContributorApplications\Pages\CreateContributorApplication;
use App\Filament\Resources\ContributorApplications\Pages\ListContributorApplications;
use App\Filament\Resources\ContributorApplications\Schemas\ContributorApplicationForm;
use App\Filament\Resources\ContributorApplications\Tables\ContributorApplicationsTable;
use App\Models\ContributorApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContributorApplicationResource extends Resource
{
    use AdminOnlyResource;

    protected static ?string $model = ContributorApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel  = 'Aplikasi Kontributor';
    protected static ?string $modelLabel       = 'Aplikasi Kontributor';
    protected static ?string $pluralModelLabel  = 'Aplikasi Kontributor';
    protected static ?int    $navigationSort   = 6;

    public static function getNavigationGroup(): ?string
    {
        return 'Moderasi';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContributorApplication::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return ContributorApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContributorApplicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListContributorApplications::route('/'),
            'create' => CreateContributorApplication::route('/create'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
