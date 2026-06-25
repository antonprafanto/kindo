<?php

namespace App\Filament\Resources\ContributorApplications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->unique(ignoreRecord: true)
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
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required()
                    ->helperText('Untuk backfill dari email: gunakan Menunggu, lalu Setujui/Tolak seperti biasa.'),
            ]);
    }
}
