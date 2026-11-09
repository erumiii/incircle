<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('circles.show', $circle) }}" class="orb orb--sm" aria-label="Kembali ke circle" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
            </a>
            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">Anggota — {{ $circle->group_name }}</h1>
        </div>

        @if (session('status') === 'member-kicked')
            <p class="mb-4 px-4 py-3 rounded-lg text-sm" style="background: #BFE3C4; color: #0B6B1C;">Anggota dikeluarkan.</p>
        @endif
        @if (session('error'))
            <p class="mb-4 px-4 py-3 rounded-lg text-sm" style="background: #F0B8B0; color: #6E0A0A;">{{ session('error') }}</p>
        @endif

        <div class="glass-card overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left" style="background: var(--bg-header);">
                        <th class="px-4 py-3 font-semibold uppercase text-xs tracking-widest" style="color: var(--text-on-light);">Username</th>
                        <th class="px-4 py-3 font-semibold uppercase text-xs tracking-widest" style="color: var(--text-on-light);">Role</th>
                        <th class="px-4 py-3 font-semibold uppercase text-xs tracking-widest" style="color: var(--text-on-light);">Bergabung</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr class="border-t border-slate-200/60">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="avatar" style="width: 36px; height: 36px; font-size: 14px;" aria-hidden="true">{{ strtoupper(substr($member->user->username, 0, 1)) }}</span>
                                    <span class="font-medium" style="color: var(--text-on-light);">{{ $member->user->username }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($member->role === 'admin')
                                    <span class="text-xs font-bold uppercase tracking-widest" style="color: #0A1A6E;">ADMIN</span>
                                @else
                                    <span class="text-xs uppercase tracking-widest text-slate-500">member</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $member->joined_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                {{-- F7: admin tidak bisa mengeluarkan diri sendiri/admin lain --}}
                                @if ($member->role !== 'admin')
                                    <form method="POST" action="{{ route('circles.members.kick', ['circle' => $circle->id, 'member' => $member->id]) }}"
                                          onsubmit="return confirm('Keluarkan {{ $member->user->username }} dari circle ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="orb orb--red orb--sm" aria-label="Keluarkan {{ $member->user->username }}" title="Keluarkan">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
