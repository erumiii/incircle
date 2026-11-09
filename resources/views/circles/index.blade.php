<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">My Circles</h1>
            <a href="{{ route('circles.create') }}" class="pill pill--follow" style="text-decoration: none;">
                Buat Circle
            </a>
        </div>

        @if (session('status') === 'circle-deleted')
            <p class="mb-4 px-4 py-3 rounded-lg text-sm" style="background: #BFE3C4; color: #0B6B1C;">Circle berhasil dihapus.</p>
        @endif

        @if ($memberships->isEmpty())
            <div class="glass-card p-10 text-center">
                <span class="orb orb--teal mx-auto mb-4" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M4.5 14c2-4 5-6 7.5-6s5.5 2 7.5 6"/></svg>
                </span>
                <p class="text-lg font-medium" style="color: var(--text-on-light);">Belum ada circle.</p>
                <p class="text-sm mt-1 text-slate-600">Buat circle pertamamu atau bergabung lewat tautan undangan teman.</p>
            </div>
        @else
            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($memberships as $membership)
                    <li>
                        <a href="{{ route('circles.show', $membership->group) }}" class="block glass-card p-5 h-full" style="text-decoration: none;">
                            <div class="flex items-center gap-3">
                                <span class="avatar" aria-hidden="true">{{ strtoupper(substr($membership->group->group_name, 0, 1)) }}</span>
                                <div>
                                    <p class="font-semibold" style="color: var(--text-on-light);">{{ $membership->group->group_name }}</p>
                                    @if ($membership->role === 'admin')
                                        <p class="text-xs mt-0.5 font-bold uppercase tracking-widest" style="color: #0A1A6E;">ADMIN</p>
                                    @else
                                        <p class="text-xs mt-0.5 uppercase tracking-widest text-slate-500">member</p>
                                    @endif
                                </div>
                            </div>
                            @if ($membership->group->description)
                                <p class="text-sm mt-3 text-slate-600 line-clamp-2">{{ $membership->group->description }}</p>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-app-layout>
