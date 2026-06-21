<x-layouts.app title="Kontak — Koding Indonesia">

    <x-breadcrumb :items="[['label' => 'Kontak']]" />

    <div class="max-w-2xl mx-auto px-4 py-16">

        <h1 class="text-4xl font-black mb-2" style="letter-spacing:-0.02em;">Hubungi Kami</h1>
        <p class="mb-10" style="color:#718096; font-family:'Inter',sans-serif;">Punya pertanyaan, saran, atau ingin berkolaborasi? Kirim pesan di bawah ini.</p>

        @if(session('success'))
        <div class="mb-8 p-5 border-2 border-black font-semibold text-sm" style="background:#ECFDF5; color:#065F46; box-shadow: 4px 4px 0 #000;">
            ✓ {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="space-y-5">
            @csrf
            {{-- Honeypot: hidden field, bots fill it, humans don't --}}
            <div style="display:none;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap kamu"
                       class="input-brutal @error('name') border-red-500 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@kamu.com"
                       class="input-brutal @error('email') border-red-500 @enderror">
                @error('email')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Subjek <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Topik pesan kamu"
                       class="input-brutal @error('subject') border-red-500 @enderror">
                @error('subject')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Pesan <span class="text-red-500">*</span></label>
                <textarea name="message" rows="6" placeholder="Tulis pesanmu di sini... (min. 20 karakter)"
                          class="input-brutal resize-none @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn-brutal btn-primary w-full py-4 text-sm">
                Kirim Pesan →
            </button>
        </form>

    </div>

</x-layouts.app>
