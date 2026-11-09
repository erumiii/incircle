<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="glass-card p-8 text-center">
            <span class="orb orb--green mx-auto mb-4" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/></svg>
            </span>

            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">Circle Invitation</h1>

            <div class="mt-6 flex items-center gap-4 justify-center">
                <span class="avatar" aria-hidden="true">{{ strtoupper(substr($circle->group_name, 0, 1)) }}</span>
                <div class="text-left">
                    <p class="font-semibold text-lg" style="color: var(--text-on-light);">{{ $circle->group_name }}</p>
                    @if ($circle->description)
                        <p class="text-sm text-slate-600">{{ $circle->description }}</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('join', $token) }}" class="mt-8">
                @csrf
                <button type="submit" class="pill">{{ __('Join') }}</button>
            </form>

            <a href="{{ route('circles.index') }}" class="block mt-4 text-sm text-slate-500 hover:text-slate-700" style="text-decoration: underline;">not now</a>
        </div>
    </div>
</x-app-layout>
