<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="glass-card p-8 text-center" style="border-color: rgba(138, 26, 20, .4);">
            <span class="orb orb--red mx-auto mb-4" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a9 9 0 100 18 9 9 0 000-18zM9 9l6 6M15 9l-6 6"/></svg>
            </span>

            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">Invalid Invitation</h1>
            <p class="text-sm mt-2 text-slate-600">
                This invitation link is expired, revoked, or never existed.
                Ask the circle admin to send a new invitation link.
            </p>

            <a href="{{ route('circles.index') }}" class="pill pill--decline mt-6" style="text-decoration: none;">{{ __('Go Back') }}</a>
        </div>
    </div>
</x-app-layout>
