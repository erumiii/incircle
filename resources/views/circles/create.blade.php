<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-semibold mb-6" style="color: var(--text-on-light);">Buat Circle</h1>

        <form method="POST" action="{{ route('circles.store') }}" class="glass-card p-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="group_name" :value="__('Nama Circle')" />
                <x-text-input id="group_name" name="group_name" type="text" class="mt-1 block w-full" :value="old('group_name')" required autofocus maxlength="100" />
                <x-input-error :messages="$errors->get('group_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
                <textarea id="description" name="description" class="mt-1 block w-full rounded-lg border-slate-300" rows="3" maxlength="500" style="background: var(--surface-input); border: 1px solid var(--border-input); border-radius: var(--radius-input); padding: 8px 12px; font: 400 15px/1.4 Verdana, sans-serif;">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="pill">{{ __('Buat') }}</button>
                <a href="{{ route('circles.index') }}" class="pill pill--decline" style="text-decoration: none;">{{ __('Batal') }}</a>
            </div>
        </form>
    </div>
</x-app-layout>
