@php
    $link = $link ?? null;
@endphp

<div class="mb-4">
    <x-forms.input label="Nama" name="nama" type="text" value="{{ old('nama', $link?->nama) }}" required maxlength="150" />
</div>

<div class="mb-4">
    <x-forms.input label="URL" name="url" type="url" value="{{ old('url', $link?->url) }}" required maxlength="500" placeholder="https://" />
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Tautan dibuka di tab baru dari menu Informasi portal anggota.') }}</p>
</div>

<div class="mb-4">
    <label for="keterangan" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Keterangan') }}</label>
    <textarea id="keterangan" name="keterangan" rows="3" maxlength="1000"
        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('keterangan', $link?->keterangan) }}</textarea>
    @error('keterangan')
        <span class="text-red-500">{{ $message }}</span>
    @enderror
</div>

<div class="mb-6">
    <label for="status" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Status') }}</label>
    <select id="status" name="status" required
        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <option value="1" @selected((string) old('status', $link?->status ?? 1) === '1')>Aktif</option>
        <option value="0" @selected((string) old('status', $link?->status ?? 1) === '0')>Tidak Aktif</option>
    </select>
    @error('status')
        <span class="text-red-500">{{ $message }}</span>
    @enderror
</div>
