<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ArticleForm
{
    /**
     * @param  bool  $includeCoverSection  Cover di form create; pada edit pakai tombol Upload Cover di daftar artikel.
     * @param  bool  $excludeBodyFromForm  Edit: isi artikel lewat editor terpisah (hindari WAF + Livewire 403).
     * @param  string|null  $bodyEditorUrl  URL editor isi saat excludeBodyFromForm aktif.
     * @param  string|null  $authorLockedStatus  Kontributor: kunci status saat artikel sudah terbit (mis. `published`).
     */
    public static function configure(
        Schema $schema,
        bool $includeCoverSection = true,
        bool $excludeBodyFromForm = false,
        ?string $bodyEditorUrl = null,
        ?string $authorLockedStatus = null,
    ): Schema {
        $isAuthor = auth()->user()?->isAuthor() ?? false;

        $statusOptions = $isAuthor
            ? [
                'draft'          => '📝 Draft',
                'pending_review' => '📤 Menunggu Review',
            ]
            : [
                'draft'          => '📝 Draft',
                'pending_review' => '📤 Menunggu Review',
                'published'      => '✅ Published',
            ];

        $categorySelect = Select::make('category_id')
            ->label('Kategori')
            ->relationship('category', 'name')
            ->searchable()
            ->preload()
            ->required();

        if (! $isAuthor) {
            $categorySelect->createOptionForm([
                TextInput::make('name')->required()->label('Nama Kategori'),
                TextInput::make('slug')->required()->label('Slug'),
            ]);
        } else {
            $categorySelect->helperText('Pilih kategori yang sudah tersedia. Lihat daftar di /menjadi-kontributor.');
        }

        $tagsSelect = Select::make('tags')
            ->label('Tag')
            ->relationship('tags', 'name')
            ->multiple()
            ->searchable()
            ->preload();

        if (! $isAuthor) {
            $tagsSelect->createOptionForm([
                TextInput::make('name')->required()->label('Nama Tag'),
                TextInput::make('slug')->required()->label('Slug'),
            ]);
        } else {
            $tagsSelect->helperText('Pilih tag yang sudah ada — hindari duplikat atau variasi nama serupa.');
        }

        $components = [];

        if ($excludeBodyFromForm) {
            $components[] = Section::make('Checklist Penulisan')
                ->description('Centang mental sebelum kirim review — metadata, isi, cover, status')
                ->schema([
                    Placeholder::make('authoring_checklist')
                        ->label('')
                        ->content(function ($record) use ($bodyEditorUrl): HtmlString {
                            if (! $record) {
                                return new HtmlString('—');
                            }

                            $hasMeta = filled($record->title)
                                && filled($record->slug)
                                && filled($record->excerpt)
                                && filled($record->category_id);
                            $hasBody = filled(trim(strip_tags((string) $record->body)));
                            $hasCover = filled($record->cover_image);
                            $statusReady = in_array($record->status, ['pending_review', 'published'], true);

                            $item = function (bool $done, string $label, ?string $hint = null): string {
                                $mark = $done ? '✅' : '☐';
                                $style = $done
                                    ? 'color:#166534;'
                                    : 'color:#4A5568;';
                                $hintHtml = $hint
                                    ? ' <span style="font-weight:400;opacity:.85;">— ' . $hint . '</span>'
                                    : '';

                                return '<li style="margin:0 0 .4rem;' . $style . '">'
                                    . '<strong>' . $mark . ' ' . e($label) . '</strong>'
                                    . $hintHtml
                                    . '</li>';
                            };

                            $isiHint = $hasBody
                                ? '<a href="' . e($bodyEditorUrl ?? '#') . '">Edit isi</a>'
                                : '<a href="' . e($bodyEditorUrl ?? '#') . '">Buka editor isi</a>';

                            $html = '<ul style="margin:0;padding:0;list-style:none;font-size:.875rem;line-height:1.45;">'
                                . $item($hasMeta, 'Metadata', 'judul, slug, ringkasan, kategori')
                                . $item($hasBody, 'Isi artikel', $isiHint)
                                . $item($hasCover, 'Cover', 'upload dari daftar artikel → Upload Cover')
                                . $item($statusReady, 'Status', $record->status === 'draft'
                                    ? 'masih draft — pilih Menunggu Review saat siap'
                                    : e($record->previewStatusLabel()))
                                . '</ul>';

                            return new HtmlString($html);
                        })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->compact();
        }

        $components[] = Section::make('Konten Artikel')
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

                    ...($excludeBodyFromForm
                        ? [
                            Placeholder::make('body_editor_link')
                                ->label('Isi Artikel')
                                ->content(new HtmlString(
                                    '<div style="display:grid;gap:.75rem;font-size:.875rem;line-height:1.5;">'
                                    . '<p style="margin:0;"><strong>Alur menyimpan (2 langkah):</strong></p>'
                                    . '<ol style="margin:0;padding-left:1.25rem;">'
                                    . '<li><strong>Isi / konten</strong> — buka editor terpisah (hindari blokir WAF hosting), lalu klik <em>Simpan Isi Artikel</em>.</li>'
                                    . '<li><strong>Metadata</strong> — judul, ringkasan, kategori, tag &amp; status disimpan lewat tombol Simpan di form ini.</li>'
                                    . '</ol>'
                                    . '<p style="margin:0;">Cover diganti dari daftar artikel → tombol <strong>Upload Cover</strong>.</p>'
                                    . '<p style="margin:.25rem 0 0;">'
                                    . '<a href="' . e($bodyEditorUrl ?? '#') . '" '
                                    . 'style="display:inline-block;padding:.5rem 1rem;font-weight:700;'
                                    . 'background:#2979FF;color:#fff;border:2px solid #000;text-decoration:none;">'
                                    . 'Buka Editor Isi Artikel →</a>'
                                    . '</p>'
                                    . '</div>'
                                ))
                                ->columnSpanFull(),
                        ]
                        : [
                            RichEditor::make('body')
                                ->label('Isi Artikel')
                                ->required()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('articles/body')
                                ->fileAttachmentsVisibility('public')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike', 'code', 'link'],
                                    ['h2', 'h3', 'h4'],
                                    ['alignStart', 'alignCenter', 'alignEnd'],
                                    ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                    ['table', 'horizontalRule', 'attachFiles'],
                                    ['undo', 'redo'],
                                ])
                                ->floatingToolbars([
                                    'paragraph' => [
                                        'bold', 'italic', 'underline', 'strike', 'code', 'link',
                                    ],
                                    'heading' => [
                                        'h2', 'h3', 'h4',
                                    ],
                                    'blockquote' => [
                                        'bold', 'italic', 'link',
                                    ],
                                    'table' => [
                                        'tableAddColumnBefore', 'tableAddColumnAfter', 'tableDeleteColumn',
                                        'tableAddRowBefore', 'tableAddRowAfter', 'tableDeleteRow',
                                        'tableMergeCells', 'tableSplitCell',
                                        'tableToggleHeaderRow', 'tableToggleHeaderCell',
                                        'tableDelete',
                                    ],
                                ])
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull();

        if ($includeCoverSection) {
            $components[] = Section::make('Gambar Sampul')
                ->description('Upload gambar cover artikel (rasio 16:9, ideal 1200×630px)')
                ->schema([
                    FileUpload::make('cover_image')
                        ->label(false)
                        ->disk('public')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('630')
                        ->directory('articles/covers')
                        ->maxSize(4096)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Ideal 1200×630px (16:9), maks. 4 MB. JPG/PNG/WebP — server otomatis konversi ke WebP.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->collapsed();
        }

        $components[] = Section::make('Metadata & Publikasi')
            ->description($isAuthor
                ? ($authorLockedStatus === 'published'
                    ? 'Artikel sudah terbit — perubahan judul, tag, dan isi langsung tampil di situs'
                    : 'Pilih kategori & tag yang sudah ada, lalu kirim untuk review saat siap')
                : 'Atur kategori, tag, status, dan jadwal terbit')
            ->schema([
                $categorySelect,
                $tagsSelect,

                ...($isAuthor && $authorLockedStatus === 'published'
                    ? [
                        Placeholder::make('status_locked')
                            ->label('Status Publikasi')
                            ->content('✅ Published — artikel aktif di situs. Perubahan tidak perlu review ulang.'),
                    ]
                    : [
                        Select::make('status')
                            ->label('Status Publikasi')
                            ->options($statusOptions)
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText($isAuthor ? 'Pilih "Menunggu Review" setelah artikel siap ditinjau admin.' : null),
                    ]),

                Textarea::make('review_notes')
                    ->label('Catatan Review')
                    ->rows(3)
                    ->maxLength(2000)
                    ->visible(fn () => ! $isAuthor)
                    ->helperText('Wajib diisi saat menolak (pending → draft). Penulis akan melihat catatan ini.')
                    ->columnSpanFull(),

                Placeholder::make('review_notes_author')
                    ->label('Catatan dari Admin')
                    ->content(fn ($record) => filled($record?->review_notes)
                        ? new HtmlString('<div style="padding:.75rem;border:2px solid #000;background:#fff3cd;font-size:.875rem;white-space:pre-wrap;">'
                            . e($record->review_notes)
                            . '</div>')
                        : '—')
                    ->visible(fn ($record) => $isAuthor && filled($record?->review_notes))
                    ->columnSpanFull(),

                DateTimePicker::make('published_at')
                    ->label('Tanggal Terbit')
                    ->default(now())
                    ->seconds(false)
                    ->visible(fn () => ! $isAuthor),

                Toggle::make('is_featured')
                    ->label('Artikel Unggulan')
                    ->helperText(function ($record) {
                        $featuredCount = \App\Models\Article::query()
                            ->featured()
                            ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                            ->count();

                        $base = 'Homepage menampilkan maks. 3 unggulan (terbaru).';

                        if ($featuredCount >= 3) {
                            return $base." Sudah ada {$featuredCount} artikel unggulan lain — yang lebih lama tidak tampil di home.";
                        }

                        return $base." Saat ini {$featuredCount}/3 slot terisi.";
                    })
                    ->visible(fn () => ! $isAuthor)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->columnSpanFull();

        $components[] = Section::make('SEO (opsional)')
            ->description('Kosongkan untuk memakai judul & ringkasan artikel secara otomatis')
            ->schema([
                TextInput::make('seo_title')
                    ->label('Judul SEO')
                    ->maxLength(70)
                    ->live(onBlur: true)
                    ->helperText(fn (?string $state): string => mb_strlen($state ?? '').'/70 karakter — tampil di tab browser & hasil pencarian')
                    ->validationMessages([
                        'max' => 'Judul SEO maksimal 70 karakter.',
                    ])
                    ->columnSpanFull(),

                Textarea::make('seo_description')
                    ->label('Meta Description')
                    ->rows(3)
                    ->maxLength(160)
                    ->live(debounce: 300)
                    ->helperText(fn (?string $state): string => mb_strlen($state ?? '').'/160 karakter — cuplikan di Google/sosial')
                    ->validationMessages([
                        'max' => 'Meta Description maksimal 160 karakter.',
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1)
            ->columnSpanFull()
            ->collapsed();

        return $schema->components($components);
    }
}
