<button
    type="button"
    @click="$store.theme.toggle()"
    class="p-2 border-2 border-black hover:bg-black hover:text-white transition-colors"
    style="box-shadow: 2px 2px 0 #000;"
    :title="$store.theme.current === 'dark' ? 'Mode terang' : 'Mode gelap'"
    :aria-label="$store.theme.current === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
>
    {{-- Sun (shown in dark mode) --}}
    <svg x-show="$store.theme.current === 'dark'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="5"/>
        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
    </svg>
    {{-- Moon (shown in light mode) --}}
    <svg x-show="$store.theme.current !== 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
</button>
