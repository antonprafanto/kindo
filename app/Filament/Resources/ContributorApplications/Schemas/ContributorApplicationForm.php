<?php

namespace App\Filament\Resources\ContributorApplications\Schemas;

use App\Models\ContributorApplication;
use App\Support\EmailNormalizer;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\ClosureValidationRule;

class ContributorApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(200)
                    ->rules([
                        fn (): ClosureValidationRule => new ClosureValidationRule(
                            function (string $attribute, mixed $value, \Closure $fail): void {
                                $normalized = EmailNormalizer::normalize((string) $value);

                                if (ContributorApplication::pending()->where('email', $normalized)->exists()) {
                                    $fail('Sudah ada aplikasi menunggu untuk email ini.');
                                }
                            }
                        ),
                    ])
                    ->helperText('Email dinormalisasi otomatis (Gmail tanpa titik).'),

                TextInput::make('topic_expertise')
                    ->label('Bidang Keahlian')
                    ->required()
                    ->maxLength(200),

                TextInput::make('sample_url')
                    ->label('Link Portofolio / Contoh Tulisan')
                    ->url()
                    ->maxLength(500),

                Textarea::make('motivation')
                    ->label('Motivasi')
                    ->required()
                    ->minLength(50)
                    ->maxLength(2000)
                    ->rows(6)
                    ->columnSpanFull()
                    ->helperText('Status akan diset Menunggu. Gunakan aksi Setujui/Tolak setelah disimpan.'),
            ]);
    }
}
