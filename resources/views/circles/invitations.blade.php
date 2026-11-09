<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('circles.show', $circle) }}" class="orb orb--sm" aria-label="Back to circle" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
            </a>
            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">Invitations — {{ $circle->group_name }}</h1>
        </div>

        @if (session('status') === 'invitation-revoked')
            <p class="mb-4 px-4 py-3 rounded-lg text-sm" style="background: #BFE3C4; color: #0B6B1C;">Invitation revoked.</p>
        @endif

        {{-- Token undangan baru: tampilkan tautan untuk dibagikan --}}
        @if (session('token'))
            <div class="glass-card p-5 mb-6" style="border-color: rgba(46, 138, 60, .45);">
                <p class="text-sm font-semibold mb-2" style="color: #0B6B1C;">New invitation link (valid for 7 days):</p>
                <div class="flex flex-wrap items-center gap-3">
                    <code class="flex-1 min-w-0 text-xs px-3 py-2 rounded-lg bg-slate-900 text-slate-100 break-all">{{ route('join.show', session('token')) }}</code>
                    <button type="button" class="pill pill--download" style="min-width: auto; height: 36px; padding: 0 20px; font-size: 15px;" onclick="navigator.clipboard.writeText('{{ route('join.show', session('token')) }}')">
                        Copy
                    </button>
                </div>
                <p class="text-xs mt-2 text-slate-600">Share this link with the friend you want to invite.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('circles.invitations.store', $circle) }}" class="mb-6">
            @csrf
            <button type="submit" class="pill pill--follow">{{ __('Create Invitation Link') }}</button>
        </form>

        @if ($invitations->isEmpty())
            <div class="glass-card p-8 text-center text-slate-600">No invitations yet.</div>
        @else
            <ul class="space-y-3">
                @foreach ($invitations as $invitation)
                    @php
                        $isRevoked = $invitation->revoked_at !== null;
                        $isExpired = $invitation->expires_at->isPast();
                        $isValid = ! $isRevoked && ! $isExpired;
                    @endphp
                    <li class="glass-card p-4 flex flex-wrap items-center justify-between gap-3 {{ $isValid ? '' : 'opacity-60' }}">
                        <div class="min-w-0">
                            <p class="text-xs font-mono break-all text-slate-500">{{ substr($invitation->token, 0, 12) }}…</p>
                            <p class="text-sm mt-1">
                                @if ($isRevoked)
                                    <span class="font-semibold" style="color: #6E0A0A;">revoked</span>
                                @elseif ($isExpired)
                                    <span class="font-semibold" style="color: #8A5A20;">expired</span>
                                @else
                                    <span class="font-semibold" style="color: #0B6B1C;">active</span>
                                @endif
                                <span class="text-slate-500"> · valid until {{ $invitation->expires_at->format('d M Y H:i') }}</span>
                            </p>
                        </div>

                        @if ($isValid)
                            <form method="POST" action="{{ route('circles.invitations.revoke', ['circle' => $circle->id, 'invitation' => $invitation->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="orb orb--red orb--sm" aria-label="Revoke invitation" title="Revoke invitation">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 5.5l13 13M18.5 5.5l-13 13"/></svg>
                                </button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
