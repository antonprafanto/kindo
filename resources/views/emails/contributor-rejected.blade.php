@extends('emails.layouts.branded')

@section('title', 'Update Aplikasi')
@section('header_bg', '#2D3748')
@section('header', 'Update Aplikasi Kontributor')

@section('content')
    <p>Halo <strong>{{ $applicantName }}</strong>,</p>
    <p>Terima kasih atas minatmu untuk berkontribusi di Koding Indonesia. Setelah meninjau aplikasimu, kami belum dapat menyetujuinya saat ini.</p>
    @if($rejectionReason)
    <div class="message-box">{{ $rejectionReason }}</div>
    @endif
    <p>Kamu <strong>boleh mengajukan ulang</strong> kapan saja setelah memperbaiki profil, portofolio, atau contoh tulisan.</p>
    @include('emails.partials.btn', ['href' => $reapplyUrl, 'label' => 'Ajukan Ulang →'])
    <p>Salam,<br><strong>Tim Koding Indonesia</strong></p>
@endsection

@section('footer')
    <p>© {{ date('Y') }} Koding Indonesia</p>
@endsection
