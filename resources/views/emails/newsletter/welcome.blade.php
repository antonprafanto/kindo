@component('emails.layouts.newsletter', [
    'headerTitle' => '🎉 Selamat Datang!',
    'headerSubtitle' => 'Newsletter aktif',
    'unsubscribeUrl' => $unsubscribeUrl,
])
<p>Halo!</p>
<p>Langganan newsletter kamu sudah <strong>aktif</strong>. Mulai sekarang kamu akan menerima email notifikasi setiap kami publish artikel baru tentang ESP32, IoT, Arduino, dan pemrograman.</p>
<p>Yang bisa kamu harapkan:</p>
<ul>
    <li>Notifikasi artikel tutorial baru</li>
    <li>Tips embedded system & IoT berbahasa Indonesia</li>
    <li>Tanpa spam — hanya saat ada konten baru</li>
</ul>
<p style="text-align:center;">
    <a href="https://kodingindonesia.com/artikel" class="btn">Jelajahi Artikel →</a>
</p>
<p class="muted">Tidak mau lagi menerima email? Gunakan link berhenti berlangganan di footer email ini.</p>
@endcomponent
