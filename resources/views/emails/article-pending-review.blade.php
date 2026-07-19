@extends('emails.layouts.branded')

@section('title', 'Artikel Menunggu Review')
@section('header_bg', '#FF7A2F')
@section('header', '📤 Artikel Menunggu Review')

@section('content')
    <p>Sebuah artikel baru dikirim untuk ditinjau dan dipublikasikan.</p>
    <div class="field">
        <div class="field-label">Judul</div>
        <div class="field-value"><strong>{{ $article->title }}</strong></div>
    </div>
    <div class="field">
        <div class="field-label">Penulis</div>
        <div class="field-value">{{ $authorName }}</div>
    </div>
    <div class="field">
        <div class="field-label">Kategori</div>
        <div class="field-value">{{ $article->category?->name ?? '—' }}</div>
    </div>
    @include('emails.partials.btn', ['href' => $adminUrl, 'label' => 'Tinjau di Panel Admin →'])
    @if(!empty($previewUrl))
    <p style="margin-top: 12px;">
        @include('emails.partials.btn', ['href' => $previewUrl, 'label' => 'Lihat Pratinjau Artikel →'])
    </p>
    <p class="muted" style="margin-top: 8px;">Link pratinjau berlaku {{ config('article.preview_ttl_days', 7) }} hari.</p>
    @endif
@endsection

@section('footer')
    <p>Koding Indonesia — Editorial Workflow</p>
@endsection
