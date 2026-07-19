@extends('emails.layouts.branded')

@section('title', 'Pesan Baru dari '.$senderName)
@section('header_bg', '#2979FF')
@section('header', '📬 Pesan Baru Masuk')
@section('subtitle', 'Koding Indonesia — Contact Form')

@section('content')
    <div class="field">
        <div class="field-label">Dari</div>
        <div class="field-value"><strong>{{ $senderName }}</strong></div>
    </div>
    <div class="field">
        <div class="field-label">Email Pengirim</div>
        <div class="field-value"><a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></div>
    </div>
    <div class="field">
        <div class="field-label">Subjek</div>
        <div class="field-value"><strong>{{ $contactSubject }}</strong></div>
    </div>
    <hr class="divider">
    <div class="field">
        <div class="field-label">Pesan</div>
        <div class="message-box">{{ $messageBody }}</div>
    </div>
    <hr class="divider">
    @if(!empty($panelUrl))
    <p style="margin: 0 0 16px;">
        @include('emails.partials.btn', ['href' => $panelUrl, 'label' => 'Buka di Panel'])
    </p>
    @endif
    @if(!empty($searchHint))
    <p class="muted">{{ $searchHint }}</p>
    @endif
    <p class="muted">
        Balas email ini untuk langsung merespons ke <strong>{{ $senderEmail }}</strong>.
    </p>
@endsection

@section('footer')
    <p>Pesan ini dikirim dari formulir kontak di <a href="https://kodingindonesia.com">kodingindonesia.com</a></p>
@endsection
