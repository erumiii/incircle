<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('circles.index') }}" class="orb orb--sm" aria-label="Back to circle list" style="text-decoration: none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
            </a>
            <h1 class="text-2xl font-semibold" style="color: var(--text-on-light);">{{ __('Edit Circle') }}</h1>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg text-sm" style="background: #F0B8B0; color: #6E0A0A;">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('circles.update', $circle) }}" class="glass-card p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <x-input-label for="group_name" :value="__('Circle Name')" />
                <x-text-input id="group_name" name="group_name" type="text" class="mt-1 block w-full" :value="old('group_name', $circle->group_name)" required maxlength="100" />
                <x-input-error :messages="$errors->get('group_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" :value="__('Description (optional)')" />
                <textarea id="description" name="description" class="mt-1 block w-full" rows="3" maxlength="500" style="background: var(--surface-input); border: 1px solid var(--border-input); border-radius: var(--radius-input); padding: 8px 12px; font: 400 15px/1.4 Verdana, sans-serif;">{{ old('description', $circle->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="pill">{{ __('Save') }}</button>
                <a href="{{ route('circles.show', $circle) }}" class="pill pill--decline" style="text-decoration: none;">{{ __('Cancel') }}</a>
            </div>
        </form>

        {{-- F6: hapus circle dengan konfirmasi mengetik nama --}}
        <div class="glass-card p-6 mt-6" style="border-color: rgba(138, 26, 20, .35);">
            <h2 class="text-lg font-semibold" style="color: #6E0A0A;">{{ __('Delete Circle') }}</h2>
            <p class="text-sm mt-1 text-slate-600">
                {{ __('Deleting permanently removes all members, messages, and invitations of this circle and cannot be undone.') }}
            </p>

            <form method="POST" action="{{ route('circles.destroy', $circle) }}" class="mt-4 space-y-3" onsubmit="return confirm('Delete {{ $circle->group_name }}? This cannot be undone.');">
                @csrf
                @method('DELETE')

                <div>
                    <x-input-label for="confirm_name" :value="__('Type the circle name to confirm')" />
                    <x-text-input id="confirm_name" name="confirm_name" type="text" class="mt-1 block w-full" required placeholder="{{ $circle->group_name }}" />
                    <x-input-error :messages="$errors->get('confirm_name')" class="mt-2" />
                </div>

                <button type="submit" class="pill pill--decline">{{ __('Delete Permanently') }}</button>
            </form>
        </div>
    </div>
</x-app-layout>
