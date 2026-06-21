<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── 1. Konten utama — penuh di atas ───────────────────────
                Section::make('Konten Artikel')
                    ->description('Tulis judul, ringkasan, dan isi artikel di sini')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefix('/')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->label('Ringkasan / Excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->hint('Maks 500 karakter — ditampilkan di listing dan meta description.')
                            ->columnSpanFull(),

                        RichEditor::make('body')
                            ->label('Isi Artikel')
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'code', 'link'],
                                ['h2', 'h3', 'h4'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                ['table', 'horizontalRule', 'attachFiles'],
                                ['undo', 'redo'],
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ─── 2. Gambar sampul ───────────────────────────────────────
                Section::make('Gambar Sampul')
                    ->description('Upload gambar cover artikel (rasio 16:9, ideal 1200×630px)')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label(false)
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('630')
                            ->directory('articles/covers')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),

                // ─── 3. Metadata publikasi — di bawah ──────────────────────
                Section::make('Metadata & Publikasi')
                    ->description('Atur kategori, tag, status, dan jadwal terbit')
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Nama Kategori'),
                                TextInput::make('slug')->required()->label('Slug'),
                            ]),

                        Select::make('tags')
                            ->label('Tag')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Nama Tag'),
                                TextInput::make('slug')->required()->label('Slug'),
                            ]),

                        Select::make('status')
                            ->label('Status Publikasi')
                            ->options([
                                'draft'     => '📝 Draft',
                                'published' => '✅ Published',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->default(now())
                            ->seconds(false),

                        Toggle::make('is_featured')
                            ->label('Artikel Unggulan')
                            ->helperText('Tampilkan di bagian featured homepage')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
