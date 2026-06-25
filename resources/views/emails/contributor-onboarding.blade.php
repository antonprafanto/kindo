<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Onboarding Kontributor</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #2D3748; background: #f5f5f0; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border: 2px solid #000; }
        .header { background: #2979FF; padding: 24px 32px; border-bottom: 2px solid #000; color: #fff; }
        .body { padding: 32px; }
        .btn { display: inline-block; margin: 16px 0; padding: 12px 20px; background: #2979FF; color: #fff; text-decoration: none; font-weight: bold; border: 2px solid #000; }
        .note { background: #F7FAFC; border-left: 4px solid #2979FF; padding: 12px 16px; margin: 16px 0; }
        .footer { background: #2D3748; padding: 16px 32px; color: rgba(255,255,255,0.7); font-size: 12px; }
        ul { padding-left: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0;font-size:20px;">Selamat Bergabung, {{ $firstName }}!</h1>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $applicantName }}</strong>,</p>
            <p>Selamat! Aplikasi kontributormu di <strong>Koding Indonesia</strong> sudah disetujui. Terima kasih sudah mau berbagi ilmu{{ $topicExpertise ? ' di bidang ' . $topicExpertise : '' }}.</p>

            @if($personalNote)
            <div class="note">{!! nl2br(e($personalNote)) !!}</div>
            @endif

            <p>Kamu seharusnya sudah menerima <strong>2 email dari sistem</strong>:</p>
            <ol>
                <li>Email untuk <strong>membuat password</strong> akun panel penulis</li>
                <li>Email konfirmasi bahwa aplikasimu <strong>disetujui</strong></li>
            </ol>
            <p>Kalau belum ada di inbox, cek folder <strong>Spam/Promosi</strong> juga ya.</p>
            <div class="note">
                <strong>Penting:</strong> Link reset password berlaku <strong>24 jam</strong> setelah email dikirim — ini terpisah dari proses review aplikasi (3–5 hari kerja).
                Kalau sudah kedaluwarsa, minta link baru di
                <a href="{{ url('/admin/password-reset/request') }}">/admin/password-reset/request</a>.
            </div>

            <h3 style="margin-top:24px;">Langkah pertama</h3>
            <ol>
                <li>Buka email reset password → klik link → buat password baru</li>
                <li>Login ke panel penulis melalui tombol di bawah</li>
                <li>Baca pedoman lengkap di <a href="{{ $guidelinesUrl }}">/menjadi-kontributor</a></li>
            </ol>

            @include('emails.partials.btn', ['href' => $loginUrl, 'label' => 'Login ke Panel Penulis →'])

            @if(!empty($topicIdeas))
            <h3 style="margin-top:24px;">Ide topik artikel pertamamu</h3>
            <ul>
                @foreach($topicIdeas as $idea)
                <li>{{ $idea }}</li>
                @endforeach
            </ul>
            <p style="font-size:14px;color:#718096;">Kamu bebas memilih topik lain — yang penting jelas, orisinal, dan bermanfaat.</p>
            @endif

            <h3 style="margin-top:24px;">Alur singkat</h3>
            <p>Tulis artikel → simpan <strong>Draft</strong> → ubah ke <strong>Menunggu Review</strong> → tim kami tinjau & publish.</p>

            <p>Ada pertanyaan? Balas email ini atau hubungi kami lewat <a href="{{ $contactUrl }}">/kontak</a>.</p>

            <p>Salam,<br><strong>{{ $senderName }}</strong><br>Koding Indonesia</p>
        </div>
        <div class="footer">© {{ date('Y') }} Koding Indonesia — {{ $guidelinesUrl }}</div>
    </div>
</body>
</html>
