@extends('emails.layouts.branded')

@section('title', 'Komentar baru menunggu moderasi')
@section('header_bg', '#2979FF')
@section('header', 'Komentar baru')
@section('subtitle', 'Menunggu moderasi')

@section('content')
    <p><strong>Artikel:</strong> {{ $articleTitle }}</p>
    <p><strong>Dari:</strong> {{ $authorName }} &lt;{{ $authorEmail }}&gt;</p>
    <div class="message-box">{{ $commentBody }}</div>
    <p>@include('emails.partials.btn', ['href' => $adminUrl, 'label' => 'Buka di panel →'])</p>
@endsection
