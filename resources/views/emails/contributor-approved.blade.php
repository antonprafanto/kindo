<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Disetujui</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #22C55E; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .body { padding: 32px; }
        .btn { display: inline-block; margin: 16px 0; padding: 12px 20px; background: #2979FF; color: #fff; text-decoration: none; font-weight: bold; border: 2px solid #000; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:20px;">🎉 Selamat, Kamu Disetujui!</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $applicantName }}</strong>,</p>
            <p>Aplikasi kontributormu telah <strong>disetujui</strong>. Selamat bergabung dengan Koding Indonesia!</p>
            <p>Kami sudah mengirim email terpisah berisi link untuk <strong>membuat password</strong> akun panel penulis. Link tersebut berlaku <strong>24 jam</strong> — klik segera setelah email masuk. Setelah login:</p>
            <ol>
                <li>Buka menu <strong>Profil Publik</strong> — lengkapi bio, foto, dan link sosial</li>
                <li>Buka menu <strong>Artikel → Tulis Artikel Baru</strong></li>
                <li>Tulis konten, pilih kategori & tag yang sudah tersedia</li>
                <li>Simpan sebagai Draft, lalu ubah status ke <strong>Menunggu Review</strong> saat siap</li>
            </ol>
            @include('emails.partials.btn', ['href' => $loginUrl, 'label' => 'Login ke Panel Penulis →'])
            <p>Baca pedoman lengkap di <a href="https://kodingindonesia.com/menjadi-kontributor">/menjadi-kontributor</a>. Portofolio publikmu nanti ada di <a href="https://kodingindonesia.com/penulis">/penulis</a>.</p>
            <p>Salam,<br><strong>Tim Koding Indonesia</strong></p>
        </div>
        <div class="footer">© {{ date('Y') }} Koding Indonesia</div>
    </div>
</body>
</html>
