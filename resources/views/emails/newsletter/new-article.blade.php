@component('emails.layouts.newsletter', [
    'headerTitle' => '📝 Artikel Baru',
    'headerSubtitle' => $article->category?->name ?? 'Koding Indonesia',
    'unsubscribeUrl' => $unsubscribeUrl,
])
<p>Halo!</p>
<p>Ada artikel baru di <strong>Koding Indonesia</strong> yang mungkin menarik untuk kamu:</p>

<div style="border: 2px solid #000; padding: 20px; margin: 20px 0; box-shadow: 4px 4px 0 #000;">
    <p style="margin: 0 0 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #FF7A2F;">
        {{ $article->category?->name ?? 'Tutorial' }} · {{ $article->read_time_minutes }} menit baca
    </p>
    <h2 style="margin: 0 0 12px; font-size: 20px; color: #2D3748;">{{ $article->title }}</h2>
    <p style="margin: 0; color: #718096; font-size: 14px;">{{ $article->excerpt }}</p>
</div>

<p style="text-align:center;">
    @include('emails.partials.btn', ['href' => route('articles.show', $article->slug), 'label' => 'Baca Artikel →'])
</p>

<p class="muted">Kamu menerima email ini karena berlangganan newsletter Koding Indonesia.</p>
@endcomponent
