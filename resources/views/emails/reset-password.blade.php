@extends('emails.layouts.branded')

@section('title', 'Buat Password — Koding Indonesia')
@section('header_bg', '#2979FF')
@section('header', 'Buat Password Akun Panel')
@section('subtitle', 'Koding Indonesia — Panel Penulis')

@section('content')
    <p>
        Halo
        @if (! empty($userName))
            , <strong>{{ $userName }}</strong>
        @endif
        ,
    </p>
    <p>Kami mengirim email ini karena akun panel penulis kamu di Koding Indonesia perlu dibuat atau diatur ulang password-nya.</p>
    @include('emails.partials.btn', ['href' => $url, 'label' => 'Buat Password →'])
    <p class="muted">Link ini berlaku selama <strong>{{ $expireText }}</strong>.</p>
    <p class="muted">Kalau kamu tidak meminta ini, abaikan email ini.</p>
@endsection

@section('footer')
    <p>© {{ date('Y') }} Koding Indonesia</p>
    <p><a href="https://kodingindonesia.com">kodingindonesia.com</a></p>
@endsection
