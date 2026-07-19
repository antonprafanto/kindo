<section class="mt-10 pt-8 border-t-2 border-black" id="komentar">
    <h2 class="text-xl font-black mb-2 theme-heading">Komentar</h2>
    <p class="text-sm theme-muted mb-6">
        Punya pertanyaan atau pengalaman? Tulis di bawah — komentar akan tampil setelah disetujui.
    </p>

    @if($successMessage)
    <div class="mb-6 p-4 border-2 border-black text-sm font-semibold" style="background:#ECFDF5; color:#065F46; box-shadow: 4px 4px 0 #000;">
        ✓ {{ $successMessage }}
    </div>
    @endif

    @if($comments->isNotEmpty())
    <div class="space-y-4 mb-8">
        @foreach($comments as $comment)
        <article class="p-4 border-2 border-black theme-paper" style="box-shadow: 3px 3px 0 #000;">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 flex-shrink-0 border-2 border-black flex items-center justify-center font-bold text-white text-sm" style="background:#2979FF;">
                    {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-bold text-sm theme-heading">{{ $comment->author_name }}</span>
                        <span class="text-xs font-mono theme-muted">{{ $comment->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    <p class="text-sm theme-body whitespace-pre-wrap break-words">{{ $comment->body }}</p>
                    <button type="button" wire:click="startReply({{ $comment->id }})"
                            class="mt-2 text-xs font-bold underline theme-muted hover:text-[#2979FF]">
                        Balas
                    </button>
                </div>
            </div>

            @if($comment->replies->isNotEmpty())
            <div class="mt-4 ml-6 sm:ml-10 space-y-3 border-l-2 border-black pl-4">
                @foreach($comment->replies as $reply)
                <div class="p-3 border-2 border-black theme-surface theme-body">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-bold text-sm theme-heading">{{ $reply->author_name }}</span>
                        <span class="text-xs font-mono theme-muted">{{ $reply->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    <p class="text-sm whitespace-pre-wrap break-words theme-body">{{ $reply->body }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </article>
        @endforeach
    </div>
    @else
    <p class="text-sm theme-muted mb-6 italic">Belum ada komentar. Jadilah yang pertama!</p>
    @endif

    <div class="p-5 sm:p-6 border-2 border-black theme-paper" style="box-shadow: 4px 4px 0 #000;">
        @if($replyingTo)
        <div class="mb-4 flex items-center justify-between gap-2 p-3 border-2 border-black text-sm theme-highlight">
            <span class="font-semibold">Membalas komentar</span>
            <button type="button" wire:click="cancelReply" class="text-xs font-bold underline">Batal</button>
        </div>
        @endif

        <form wire:submit="submit" class="space-y-4">
            <div style="display:none;" aria-hidden="true">
                <input type="text" wire:model="hp_fax" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="comment-author-name" class="block text-xs font-bold uppercase tracking-wider mb-2 theme-heading">Nama</label>
                    <input type="text" id="comment-author-name" wire:model="author_name" required maxlength="100"
                           class="input-brutal w-full @error('author_name') border-red-500 @enderror"
                           placeholder="Nama kamu"
                           @error('author_name') aria-invalid="true" @enderror>
                    @error('author_name') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="comment-author-email" class="block text-xs font-bold uppercase tracking-wider mb-2 theme-heading">Email</label>
                    <input type="email" id="comment-author-email" wire:model="author_email" required maxlength="200"
                           class="input-brutal w-full @error('author_email') border-red-500 @enderror"
                           placeholder="email@kamu.com"
                           @error('author_email') aria-invalid="true" @enderror>
                    @error('author_email') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="comment-body" class="block text-xs font-bold uppercase tracking-wider mb-2 theme-heading">Komentar</label>
                <textarea id="comment-body" wire:model="body" required rows="4" maxlength="2000"
                          class="input-brutal w-full resize-y @error('body') border-red-500 @enderror"
                          placeholder="Tulis pertanyaan atau pengalaman kamu..."
                          @error('body') aria-invalid="true" @enderror></textarea>
                @error('body') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

            @if(config('services.turnstile.site_key'))
            <div wire:ignore id="comment-turnstile-wrap" @class(['hidden' => ! $turnstileRequested])>
                <p class="text-sm font-semibold mb-2 theme-heading">Verifikasi keamanan</p>
                <div id="comment-turnstile"></div>
                @error('turnstile') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>
            @endif

            <button type="submit" class="btn-brutal btn-primary px-6 py-3 text-sm" wire:loading.attr="disabled">
                @if($turnstileRequested)
                <span wire:loading.remove wire:target="submit">Selesaikan verifikasi di atas ↑</span>
                @else
                <span wire:loading.remove wire:target="submit">Kirim Komentar →</span>
                @endif
                <span wire:loading wire:target="submit">Memproses...</span>
            </button>

            <p class="text-xs theme-muted">
                Email tidak dipublikasikan. Komentar dimoderasi sebelum tampil.
                <a href="{{ route('privacy') }}" class="underline">Kebijakan Privasi</a>
            </p>
        </form>
    </div>
</section>

@assets
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endassets

@script
<script>
    const turnstileSiteKey = @js(config('services.turnstile.site_key'));
    let turnstileWidgetId = null;

    function onCommentTurnstileSuccess(token) {
        $wire.set('turnstileToken', token).then(() => $wire.submit());
    }

    function renderCommentTurnstile() {
        const wrap = document.getElementById('comment-turnstile-wrap');
        const el = document.getElementById('comment-turnstile');
        if (! wrap || ! el) return;

        wrap.classList.remove('hidden');

        if (typeof turnstile === 'undefined') {
            setTimeout(renderCommentTurnstile, 100);
            return;
        }

        if (turnstileWidgetId !== null) {
            turnstile.remove(turnstileWidgetId);
            turnstileWidgetId = null;
        }

        el.innerHTML = '';
        turnstileWidgetId = turnstile.render(el, {
            sitekey: turnstileSiteKey,
            theme: 'auto',
            callback: onCommentTurnstileSuccess,
        });
    }

    $wire.on('render-turnstile', () => renderCommentTurnstile());

    $wire.on('reset-turnstile', () => {
        if (turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
            turnstile.remove(turnstileWidgetId);
            turnstileWidgetId = null;
        }
        const el = document.getElementById('comment-turnstile');
        if (el) el.innerHTML = '';
        document.getElementById('comment-turnstile-wrap')?.classList.add('hidden');
        $wire.set('turnstileToken', '');
    });
</script>
@endscript
