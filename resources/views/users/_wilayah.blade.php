<style>[x-cloak]{display:none!important}</style>
@php
    $roleAreas = $roles->mapWithKeys(fn ($role) => [$role->id => $role->area])->all();
    $selectedRoles = old('roles', isset($user) ? $user->roles->pluck('id')->map(fn ($id) => (string) $id)->all() : []);
@endphp

<div class="mb-6" x-data="{
    selected: @js($selectedRoles).map(String),
    areas: @js($roleAreas),
    pd: @js(old('pd_kode', $user->pd_kode ?? '')),
    pc: @js(old('pc_kode', $user->pc_kode ?? '')),
    kabupaten: @js($kabupatenOptions),
    wilayahUrl: @js(route('daftar.wilayah')),
    get needsDaerah() {
        return this.selected.some((id) => this.areas[id] === 'daerah' || this.areas[id] === 'cabang');
    },
    get needsCabang() {
        return this.selected.some((id) => this.areas[id] === 'cabang');
    },
    toggle(id, checked) {
        id = String(id);
        if (checked) {
            if (!this.selected.includes(id)) this.selected.push(id);
        } else {
            this.selected = this.selected.filter((item) => item !== id);
        }
    },
    async onProvinsi() {
        this.pc = '';
        this.kabupaten = [];
        if (!this.pd) return;
        const response = await fetch(this.wilayahUrl + '?parent=' + encodeURIComponent(this.pd));
        this.kabupaten = await response.json();
    }
}">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        {{ __('Roles') }}
    </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-900 p-4 rounded-md">
        @forelse($roles as $role)
            @continue($role->isSuperAdmin() && ! auth()->user()->isSuperAdmin())
            <div x-on:change="toggle('{{ $role->id }}', $event.target.checked)">
                <x-forms.checkbox
                    name="roles[]"
                    value="{{ $role->id }}"
                    label="{{ $role->name }} ({{ $role->areaLabel() }})"
                    :checked="in_array($role->id, old('roles', isset($user) ? $user->roles->pluck('id')->all() : []))" />
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No roles available.') }}</p>
        @endforelse
    </div>
    @error('roles')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" x-show="needsDaerah" x-cloak>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provinsi (PD)</label>
            <select name="pd_kode" x-model="pd" @change="onProvinsi()" class="w-full rounded-lg">
                <option value="">Pilih provinsi</option>
                @foreach ($provinsiOptions as $item)
                    <option value="{{ $item->kode }}">{{ $item->nama }}</option>
                @endforeach
            </select>
            @error('pd_kode')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div x-show="needsCabang">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kabupaten/Kota (PC)</label>
            <select name="pc_kode" x-model="pc" class="w-full rounded-lg">
                <option value="">Pilih kabupaten/kota</option>
                <template x-for="item in kabupaten" :key="item.kode">
                    <option :value="item.kode" x-text="item.nama" :selected="pc === item.kode"></option>
                </template>
            </select>
            @error('pc_kode')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
