<div class="space-y-4 text-sm">
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Email</div>
        <div>{{ $record->email }}</div>
    </div>
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Bidang Keahlian</div>
        <div>{{ $record->topic_expertise }}</div>
    </div>
    @if($record->sample_url)
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Portofolio</div>
        <div><a href="{{ $record->sample_url }}" target="_blank" class="text-primary-600 underline">{{ $record->sample_url }}</a></div>
    </div>
    @endif
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Motivasi</div>
        <div class="mt-1 whitespace-pre-wrap rounded-lg bg-gray-50 p-3 dark:bg-gray-800">{{ $record->motivation }}</div>
    </div>
    @if($record->rejection_reason)
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Alasan Penolakan</div>
        <div class="mt-1 whitespace-pre-wrap">{{ $record->rejection_reason }}</div>
    </div>
    @endif
    @if($record->status === 'approved')
    <div>
        <div class="text-xs font-semibold uppercase text-gray-500">Email Onboarding</div>
        <div>
            @if($record->onboarding_email_sent_at)
                Terkirim {{ $record->onboarding_email_sent_at->format('d M Y H:i') }}
            @else
                Belum dikirim
            @endif
        </div>
    </div>
    @endif
</div>
