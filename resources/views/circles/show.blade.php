<x-app-layout>
    <x-slot name="bodyClass">overflow-hidden</x-slot>

    {{-- Halaman chat terkunci pada tinggi viewport: panel input tetap diam,
        hanya area pesan hitam yang scroll (perbaikan perilaku scroll). --}}
    <div class="flex flex-col" style="height: calc(100vh - 4rem); overflow: hidden;">
        {{-- Header circle --}}
        <div class="border-b border-sky-200/50" style="background: var(--bg-header);">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex flex-wrap items-center gap-3">
                <a href="{{ route('circles.index') }}" class="orb orb--sm" aria-label="Kembali ke daftar circle" style="text-decoration: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
                </a>

                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-semibold truncate" style="color: var(--text-on-light);">{{ $circle->group_name }}</h1>
                    @if ($circle->description)
                        <p class="text-xs text-slate-500 truncate">{{ $circle->description }}</p>
                    @endif
                </div>

                @if ($role === 'admin')
                    <a href="{{ route('circles.invitations', $circle) }}" class="orb orb--olive" aria-label="Kelola undangan" style="text-decoration: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                    </a>
                    <a href="{{ route('circles.members', $circle) }}" class="orb orb--purple" aria-label="Kelola anggota" style="text-decoration: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c1-3.5 3.5-5 6.5-5s5.5 1.5 6.5 5"/><circle cx="17.5" cy="9" r="2.5"/><path d="M16 15.5c2.5 0 4.5 1.5 5.5 4.5"/></svg>
                    </a>
                    <a href="{{ route('circles.edit', $circle) }}" class="orb orb--pink" aria-label="Pengaturan circle" style="text-decoration: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 9 19.4a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6h.09A1.7 1.7 0 0 0 10 3.04V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9v.09a1.7 1.7 0 0 0 1.56 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1z"/></svg>
                    </a>
                @endif
            </div>
        </div>

        {{-- Panel input (DESIGN.md 6.6: di atas, terang) --}}
        <div class="chat-panel">
            <form method="POST" action="{{ route('circles.messages.store', ['circle' => $circle->id, 'page' => $messages->lastPage()]) }}" class="max-w-5xl mx-auto px-4 sm:px-6">
                @csrf

                <label for="message" class="sr-only">{{ __('Tulis pesan') }}</label>
                <textarea id="message" name="message" class="chat-input" maxlength="2000" required placeholder="tulis pesan di {{ $circle->group_name }}..." data-autofocus-on-error></textarea>

                @error('message')
                    <p class="mt-2 text-sm" style="color: #6E0A0A;">{{ $message }}</p>
                @enderror

                <div class="flex justify-center mt-3">
                    <button type="submit" class="btn-post">Post</button>
                </div>
            </form>
        </div>

        {{-- Area percakapan (DESIGN.md 6.4/6.5/8: hitam pekat, avatar + bubble) --}}
        <div class="chat-bg flex-1 overflow-y-auto" id="chat-scroll" data-circle-id="{{ $circle->id }}">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6" style="margin-left: 0;" id="chat-messages">

                @if (session('status') === 'circle-updated')
                    <p class="mb-4 text-sm" style="color: var(--text-muted);">Circle diperbarui.</p>
                @endif

                {{-- Template bubble realtime: markup identik dengan render server --}}
                <template id="chat-bubble-template">
                    <div class="flex items-start gap-3 mb-3 bubble-in" style="margin-left: 100px;">
                        <div class="flex-none text-center">
                            <div class="avatar" aria-hidden="true"></div>
                            <div class="avatar-name"></div>
                        </div>
                        <div class="flex-1 min-w-0 pt-2">
                            <div class="bubble">
                                <span></span>
                            </div>
                            <p class="mt-1 text-xs" style="color: var(--text-muted); letter-spacing: 0;"></p>
                        </div>
                    </div>
                </template>

                @forelse ($messages as $message)
                    <div class="flex items-start gap-3 mb-3" data-message-id="{{ $message->id }}" style="margin-left: 100px;">
                        @php
                            $isMine = $message->sender_id === auth()->id();
                            $initial = $message->sender?->username ? strtoupper(substr($message->sender->username, 0, 1)) : '?';
                        @endphp

                        <div class="flex-none text-center">
                            <div class="avatar" aria-hidden="true">{{ $initial }}</div>
                            <div class="avatar-name">{{ $message->sender?->username ?? 'unknown' }}</div>
                        </div>

                        <div class="flex-1 min-w-0 pt-2">
                            <div class="bubble {{ $isMine ? 'bubble--grey' : '' }}">
                                <span>{{ $message->message }}</span>
                            </div>
                            <p class="mt-1 text-xs" style="color: var(--text-muted); letter-spacing: 0;">
                                {{ $message->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-10" id="chat-empty" style="color: var(--text-muted);">
                        belum ada pesan. mulai obrolan pertama di circle ini.
                    </p>
                @endforelse

                @if ($messages->hasPages())
                    <div class="mt-6" style="margin-left: 100px;">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Listener realtime: pesan baru dari anggota lain langsung tampil --}}
    <script type="module">
        const container = document.getElementById('chat-messages');
        const scroller = document.getElementById('chat-scroll');
        const template = document.getElementById('chat-bubble-template');
        if (window.Echo && container && template) {
            window.Echo.private(`circle.${scroller.dataset.circleId}`)
                .listen('.message.sent', (e) => {
                    // pesan pengirim sendiri muncul via reload form POST — lewati duplikat
                    if (e.sender_id === {{ auth()->id() }}) return;
                    if (document.querySelector(`[data-message-id="${e.id}"]`)) return;

                    const empty = document.getElementById('chat-empty');
                    if (empty) empty.remove();

                    const node = template.content.firstElementChild.cloneNode(true);
                    node.dataset.messageId = e.id;
                    const isMine = e.sender_id === {{ auth()->id() }};
                    node.querySelector('.avatar').textContent = (e.username || '?').charAt(0).toUpperCase();
                    node.querySelector('.avatar-name').textContent = e.username || 'unknown';
                    const bubble = node.querySelector('.bubble');
                    if (isMine) bubble.classList.add('bubble--grey');
                    bubble.querySelector('span').textContent = e.message;
                    node.querySelector('p.mt-1').textContent = new Date(e.created_at)
                        .toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
                        .replace(/\./g, ':');

                    container.appendChild(node);
                    scroller.scrollTop = scroller.scrollHeight;
                });
        }
    </script>
</x-app-layout>
