@extends('emails.layouts.branded')

@section('title', 'Aplikasi Disetujui')
@section('header_bg', '#22C55E')
@section('header', '🎉 Selamat, Kamu Disetujui!')

@section('content')
    <p>Halo <strong>{{ $applicantName }}</strong>,</p>
    <p>Aplikasi kontributormu telah <strong>disetujui</strong>. Selamat bergabung dengan Koding Indonesia!</p>
    <p>Kami sudah mengirim email terpisah berisi link untuk <strong>membuat password</strong> akun panel penulis. Link tersebut berlaku <strong>24 jam</strong> — klik segera setelah email masuk. Setelah login:</p>
    <ol>
        <li>Buka menu <strong>Profil Publik</strong> — lengkapi bio, foto, dan link sosial</li>
        <li>Buka menu <strong>Artikel → Tulis Artikel Baru</strong></li>
        <li>Tulis konten, pilih kategori &amp; tag yang sudah tersedia</li>
        <li>Simpan sebagai Draft, lalu ubah status ke <strong>Menunggu Review</strong> saat siap</li>
    </ol>
    @include('emails.partials.btn', ['href' => $loginUrl, 'label' => 'Login ke Panel Penulis →'])
    <p>Baca pedoman lengkap di <a href="https://kodingindonesia.com/menjadi-kontributor">/menjadi-kontributor</a>. Portofolio publikmu nanti ada di <a href="https://kodingindonesia.com/penulis">/penulis</a>.</p>
    <p>Salam,<br><strong>Tim Koding Indonesia</strong></p>
@endsection

@section('footer')
    <p>© {{ date('Y') }} Koding Indonesia</p>
@endsection
