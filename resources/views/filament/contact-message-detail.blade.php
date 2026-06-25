<div class="space-y-4 text-sm">
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Email</div>
        <div><a href="mailto:{{ $record->email }}" class="text-primary-600 underline">{{ $record->email }}</a></div>
    </div>
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Subjek</div>
        <div class="font-semibold">{{ $record->subject }}</div>
    </div>
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Pesan</div>
        <div class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 dark:bg-gray-800">{{ $record->message }}</div>
    </div>
    @if($record->is_contributor_inquiry)
    <div class="rounded-lg border border-info-300 bg-info-50 p-3 text-info-800 dark:border-info-700 dark:bg-info-950 dark:text-info-200">
        <strong>Pesan terkait kontributor.</strong>
        @if($application)
            Aplikasi formal ditemukan: status <strong>{{ match($application->status) { 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => $application->status } }}</strong>
            ({{ $application->created_at->format('d M Y') }}).
            <a href="{{ url('/admin/contributor-applications') }}" class="underline">Buka Aplikasi Kontributor →</a>
        @else
            Belum ada aplikasi di formulir resmi.
            <a href="{{ route('contributor.apply') }}" target="_blank" class="underline">/menjadi-kontributor</a>
        @endif
        @if($record->auto_reply_sent_at)
            <div class="mt-1 text-xs">Auto-reply formulir kontributor terkirim {{ $record->auto_reply_sent_at->format('d M Y H:i') }}.</div>
        @endif
    </div>
    @endif
    <div class="text-xs text-gray-500">
        Dikirim {{ $record->created_at->format('d M Y H:i') }}
        @if($record->ip_address) · IP {{ $record->ip_address }} @endif
    </div>
</div>
