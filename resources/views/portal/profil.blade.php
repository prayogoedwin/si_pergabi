<x-layouts.portal title="Profil">
    <div class="mb-4">
        <h1 class="font-display text-3xl text-navy-900">Profil</h1>
        <p class="text-sm text-navy-800/70 mt-1">Ganti foto dan kata sandi akun Anda.</p>
    </div>

    <section class="rounded-2xl bg-white border border-gold-400/25 p-4 sm:p-5">
        <div class="flex items-center gap-4">
            <div class="h-20 w-20 rounded-2xl overflow-hidden bg-cream-100 border border-gold-400/30">
                @if ($anggota->foto_path)
                    <img src="{{ route('portal.foto') }}" alt="Foto {{ $anggota->nama }}" class="h-full w-full object-cover">
                @endif
            </div>
            <div>
                <p class="font-semibold">{{ $anggota->namaLengkap() }}</p>
                <p class="text-sm text-navy-800/60">{{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('portal.foto.update') }}" enctype="multipart/form-data" class="mt-4">
            @csrf
            <label class="block text-sm font-medium mb-1">Ganti foto</label>
            <input type="file" name="foto" accept="image/*" required class="block w-full text-sm">
            @error('foto')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <button type="submit" class="mt-3 rounded-xl bg-saffron-600 text-white px-4 py-2 text-sm font-medium">Simpan foto</button>
        </form>
    </section>

    <section class="mt-4 rounded-2xl bg-white border border-gold-400/25 p-4 sm:p-5">
        <h2 class="font-semibold">Ganti password</h2>
        <form method="POST" action="{{ route('portal.password') }}" class="mt-4 space-y-3 max-w-md">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm mb-1">Password saat ini</label>
                <input type="password" name="current_password" required class="w-full rounded-xl border border-navy-800/15 px-3 py-2.5 bg-cream-50">
                @error('current_password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Password baru</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-navy-800/15 px-3 py-2.5 bg-cream-50">
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm mb-1">Ulangi password baru</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-xl border border-navy-800/15 px-3 py-2.5 bg-cream-50">
            </div>
            <button type="submit" class="rounded-xl bg-navy-900 text-cream-100 px-4 py-2.5 text-sm font-medium">Perbarui password</button>
        </form>
    </section>
</x-layouts.portal>
