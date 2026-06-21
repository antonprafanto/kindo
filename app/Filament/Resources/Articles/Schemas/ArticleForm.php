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
                Section::make('Konten Artikel')
                    ->description('Isi utama artikel')
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
                            ->hint('Maks 500 karakter. Ditampilkan di listing dan meta description.')
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
                    ->columns(2),

                Section::make('Metadata')
                    ->description('Pengaturan kategori, tag, dan publikasi')
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')->required(),
                            ]),

                        Select::make('tags')
                            ->label('Tag')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')->required(),
                            ]),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publish')
                            ->default(now()),

                        Toggle::make('is_featured')
                            ->label('Artikel Unggulan')
                            ->helperText('Tampilkan di bagian hero/featured homepage'),
                    ])
                    ->columns(2),

                Section::make('Gambar Sampul')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('630')
                            ->directory('articles/covers')
                            ->hint('Ukuran ideal: 1200×630px (rasio 16:9)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
