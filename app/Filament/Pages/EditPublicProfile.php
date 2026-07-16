<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SitemapService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @property-read Schema $form
 */
class EditPublicProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = 'Profil Publik';

    protected static ?string $title = 'Profil Publik';

    protected static ?string $slug = 'profil-publik';

    protected static ?int $navigationSort = 2;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isAdmin() || $user->isAuthor());
    }

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (blank($user->slug)) {
            $user->ensureSlug();
            $user->refresh();
        }

        $this->form->fill([
            'name' => $user->name,
            'slug' => $user->slug,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
            'expertise' => $user->expertise,
            'github_url' => $user->github_url,
            'linkedin_url' => $user->linkedin_url,
            'website_url' => $user->website_url,
            'external_works' => $user->external_works ?? [],
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas')
                    ->description('Nama dan URL halaman publikmu di Koding Indonesia')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Tampil')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old): void {
                                if (blank($state)) {
                                    return;
                                }

                                $currentSlug = $get('slug');
                                $oldSlug = filled($old) ? Str::slug($old) : null;

                                // Hanya auto-update slug jika masih kosong atau masih mirror nama lama
                                // (agar slug yang sudah dikustomisasi tidak tertimpa).
                                if (blank($currentSlug) || $currentSlug === $oldSlug) {
                                    $set('slug', User::generateUniqueSlug($state, $this->getUser()->id));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->maxLength(100)
                            ->prefix('/penulis/')
                            ->helperText('Huruf kecil, angka, dan tanda hubung. Mengubah slug akan memutus link lama.')
                            ->rules([
                                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                Rule::unique('users', 'slug')->ignore($this->getUser()->id),
                            ]),

                        FileUpload::make('avatar')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->directory('avatars')
                            ->maxSize(2048)
                            ->helperText('JPG/PNG, maks 2 MB'),

                        Textarea::make('bio')
                            ->label('Bio')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('Ceritakan singkat tentang dirimu (maks 1000 karakter)')
                            ->columnSpanFull(),

                        TextInput::make('expertise')
                            ->label('Keahlian')
                            ->maxLength(150)
                            ->placeholder('Contoh: ESP32, Laravel, IoT')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Link Sosial')
                    ->schema([
                        TextInput::make('github_url')
                            ->label('GitHub')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://github.com/username'),

                        TextInput::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://linkedin.com/in/username'),

                        TextInput::make('website_url')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Karya Eksternal')
                    ->description('Proyek, repo, atau tulisan di luar Koding Indonesia')
                    ->schema([
                        Repeater::make('external_works')
                            ->label(false)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(150),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->url()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->label('Deskripsi singkat')
                                    ->rows(2)
                                    ->maxLength(300)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah karya')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment(Alignment::Start)
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Profil')
                ->submit('save')
                ->keyBindings(['mod+s']),
            Action::make('preview')
                ->label('Lihat Halaman Publik')
                ->url(fn (): ?string => $this->getPublicProfileUrl(), shouldOpenInNewTab: true)
                ->color('gray')
                ->visible(fn (): bool => filled($this->getPublicProfileUrl())),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = $this->getUser();
        $user->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'avatar' => $data['avatar'] ?? null,
            'bio' => $data['bio'] ?? null,
            'expertise' => $data['expertise'] ?? null,
            'github_url' => $data['github_url'] ?? null,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'external_works' => array_values($data['external_works'] ?? []),
        ]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable) {
            // Sitemap refresh is best-effort; profile save must not fail.
        }

        Notification::make()
            ->title('Profil publik berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function getPublicProfileUrl(): ?string
    {
        $slug = $this->data['slug'] ?? $this->getUser()->slug;

        if (blank($slug)) {
            return null;
        }

        return route('authors.show', $slug);
    }

    public function getSubheading(): ?string
    {
        $url = $this->getPublicProfileUrl();

        if (! $url) {
            return 'Lengkapi profilmu agar tampil di halaman publik kontributor.';
        }

        return 'Halaman publik: '.$url;
    }
}
