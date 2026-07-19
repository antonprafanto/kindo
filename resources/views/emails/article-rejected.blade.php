@extends('emails.layouts.branded')

@section('title', 'Artikel perlu revisi')
@section('header_bg', '#FFD600')
@section('header_color', '#000000')
@section('subtitle_color', '#2D3748')
@section('header', 'Artikel perlu revisi')
@section('subtitle', 'Koding Indonesia')

@section('content')
    <p>Halo <strong>{{ $authorName }}</strong>,</p>
    <p>Artikel <strong>{{ $article->title }}</strong> dikembalikan ke Draft dengan catatan berikut:</p>
    <div class="message-box" style="background: #fff3cd;">{{ $reviewNotes }}</div>
    <p>@include('emails.partials.btn', ['href' => $editUrl, 'label' => 'Buka artikel di panel →'])</p>
    <p class="muted">Setelah diperbaiki, kirim ulang dengan status <strong>Menunggu Review</strong>.</p>
@endsection
