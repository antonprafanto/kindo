@component('emails.layouts.newsletter', [
    'headerTitle' => '✉️ Konfirmasi Langganan',
    'headerSubtitle' => 'Satu langkah lagi!',
])
<p>Halo!</p>
<p>Kamu hampir selesai mendaftar newsletter <strong>Koding Indonesia</strong>. Klik tombol di bawah untuk mengonfirmasi email <strong>{{ $email }}</strong>:</p>
<p style="text-align:center;">
    @include('emails.partials.btn', ['href' => $confirmUrl, 'label' => 'Konfirmasi Langganan →'])
</p>
<p class="muted">Link ini berlaku untuk konfirmasi langganan. Jika kamu tidak mendaftar, abaikan email ini.</p>
<p class="muted">Atau salin link ini ke browser:<br>{{ $confirmUrl }}</p>
@endcomponent
