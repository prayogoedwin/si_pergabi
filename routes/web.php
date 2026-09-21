<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CacheController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KtaVerifikasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WilayahController;
use App\Models\Anggota;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('daftar/wilayah', [PendaftaranController::class, 'wilayah'])->name('daftar.wilayah');

Route::middleware('guest')->group(function () {
    Route::get('daftar', [PendaftaranController::class, 'create'])->name('daftar');
    Route::get('register', [PendaftaranController::class, 'create'])->name('register');
    Route::post('daftar', [PendaftaranController::class, 'store'])->middleware('throttle:8,1')->name('daftar.store');
    Route::post('register', [PendaftaranController::class, 'store'])->middleware('throttle:8,1');
});

Route::get('verifikasi-qr-anggota', [KtaVerifikasiController::class, 'show'])->name('kta.verifikasi');
Route::get('verifikasi-qr-anggota/foto', [KtaVerifikasiController::class, 'foto'])->name('kta.foto');
Route::get('verifikasi_qr-anggota', function () {
    return redirect()->route('kta.verifikasi', request()->query());
});
Route::get('verifikasi_qr-anggota/foto', function () {
    return redirect()->route('kta.foto', request()->query());
});
Route::get('verifikasi/{nomor}', function (string $nomor) {
    return redirect(Anggota::urlVerifikasiQrFor($nomor));
})->where('nomor', '[0-9.]+');
Route::get('verifikasi/{nomor}/foto', function (string $nomor) {
    return redirect(Anggota::urlFotoVerifikasiQrFor($nomor));
})->where('nomor', '[0-9.]+');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
    Route::put('settings/appearance', [Settings\AppearanceController::class, 'update'])->name('settings.appearance.update');

    Route::get('settings/pendaftaran', [Settings\PendaftaranSettingController::class, 'edit'])->name('settings.pendaftaran.edit')->middleware('permission:view-pendaftaran');
    Route::put('settings/pendaftaran', [Settings\PendaftaranSettingController::class, 'update'])->name('settings.pendaftaran.update')->middleware('permission:edit-pendaftaran');
    Route::get('settings/integrasi', [Settings\IntegrasiSettingController::class, 'edit'])->name('settings.integrasi.edit')->middleware('permission:view-integrasi');
    Route::put('settings/integrasi', [Settings\IntegrasiSettingController::class, 'update'])->name('settings.integrasi.update')->middleware('permission:edit-integrasi');
    Route::post('settings/integrasi/test-smtp', [Settings\IntegrasiSettingController::class, 'testSmtp'])->name('settings.integrasi.test-smtp')->middleware('permission:edit-integrasi');
    Route::get('settings/organisasi', [Settings\OrganisasiSettingController::class, 'edit'])->name('settings.organisasi.edit')->middleware('permission:view-organisasi');
    Route::put('settings/organisasi', [Settings\OrganisasiSettingController::class, 'update'])->name('settings.organisasi.update')->middleware('permission:edit-organisasi');
    Route::get('cache', [CacheController::class, 'index'])->name('cache.index')->middleware('permission:view-cache');
    Route::post('cache/flush', [CacheController::class, 'flush'])->name('cache.flush')->middleware('permission:view-cache');
    Route::post('cache/prefix', [CacheController::class, 'destroyPrefix'])->name('cache.destroy-prefix')->middleware('permission:view-cache');
    Route::post('cache/key', [CacheController::class, 'destroy'])->name('cache.destroy')->middleware('permission:view-cache');

    // Roles Management - Super Admin only
    Route::middleware('role:'.Role::SUPER_ADMIN)->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
        Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export')->middleware('permission:download-roles');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:create-roles');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create-roles');
        Route::patch('roles/{role}/toggle', [RoleController::class, 'toggle'])->name('roles.toggle')->middleware('permission:edit-roles');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show')->middleware('permission:show-roles');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:edit-roles');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:edit-roles');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete-roles');
    });

    // Permissions Management - dengan permission check
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:view-permissions');
    Route::get('permissions/export', [PermissionController::class, 'export'])->name('permissions.export')->middleware('permission:download-permissions');
    Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:create-permissions');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:create-permissions');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show')->middleware('permission:show-permissions');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:edit-permissions');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:edit-permissions');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete-permissions');

    // Users Management — Super Admin dan Admin PP saja
    Route::middleware('role:'.Role::SUPER_ADMIN.','.Role::ADMIN_PP)->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-users');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export')->middleware('permission:download-users');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-users');
        Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-users');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password')->middleware('permission:edit-users');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:show-users');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-users');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-users');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete-users');
    });

    // Master data - Super Admin and Admin PP only
    Route::middleware('role:'.Role::SUPER_ADMIN.','.Role::ADMIN_PP)->group(function () {
        Route::get('area', [AreaController::class, 'index'])->name('area.index')->middleware('permission:view-area');

        Route::get('wilayah', [WilayahController::class, 'index'])->name('wilayah.index')->middleware('permission:view-wilayah');
        Route::get('wilayah/export', [WilayahController::class, 'export'])->name('wilayah.export')->middleware('permission:download-wilayah');
        Route::get('wilayah/create', [WilayahController::class, 'create'])->name('wilayah.create')->middleware('permission:create-wilayah');
        Route::post('wilayah', [WilayahController::class, 'store'])->name('wilayah.store')->middleware('permission:create-wilayah');
        Route::get('wilayah/{wilayah}', [WilayahController::class, 'show'])->name('wilayah.show')->middleware('permission:show-wilayah')->where('wilayah', '[0-9.]+');
        Route::get('wilayah/{wilayah}/edit', [WilayahController::class, 'edit'])->name('wilayah.edit')->middleware('permission:edit-wilayah')->where('wilayah', '[0-9.]+');
        Route::put('wilayah/{wilayah}', [WilayahController::class, 'update'])->name('wilayah.update')->middleware('permission:edit-wilayah')->where('wilayah', '[0-9.]+');
        Route::delete('wilayah/{wilayah}', [WilayahController::class, 'destroy'])->name('wilayah.destroy')->middleware('permission:delete-wilayah')->where('wilayah', '[0-9.]+');
    });

    Route::middleware(['verified'])->group(function () {
        Route::get('portal', [PortalController::class, 'show'])->name('portal.show');
        Route::get('portal/kegiatan', [PortalController::class, 'kegiatan'])->name('portal.kegiatan');
        Route::get('portal/kegiatan/{post}', [PortalController::class, 'kegiatanShow'])->name('portal.kegiatan.show')->whereNumber('post');
        Route::get('portal/kta', [PortalController::class, 'kta'])->name('portal.kta');
        Route::get('portal/qr', [PortalController::class, 'qr'])->name('portal.qr');
        Route::get('portal/profil', [PortalController::class, 'profil'])->name('portal.profil');
        Route::put('portal/password', [PortalController::class, 'updatePassword'])->name('portal.password');
        Route::post('portal/foto', [PortalController::class, 'updateFoto'])->name('portal.foto.update');
        Route::get('portal/foto', [PortalController::class, 'foto'])->name('portal.foto');
        Route::get('keanggotaan', fn () => redirect()->route('portal.show'))->name('keanggotaan.show');
    });

    Route::get('kegiatan', [KegiatanController::class, 'index'])->middleware('verified')->name('kegiatan.index');
    Route::get('kegiatan/{post}', [KegiatanController::class, 'show'])->middleware('verified')->name('kegiatan.show')->whereNumber('post');

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index')->middleware('permission:view-laporan');
    Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export')->middleware('permission:download-laporan');

    Route::get('anggota', [AnggotaController::class, 'index'])->name('anggota.index')->middleware('permission:view-anggota');
    Route::get('anggota/{anggota}', [AnggotaController::class, 'show'])->name('anggota.show')->middleware('permission:show-anggota');
    Route::get('anggota/{anggota}/qr', [AnggotaController::class, 'qr'])->name('anggota.qr')->middleware('permission:show-anggota');
    Route::post('anggota/{anggota}/reset-password', [AnggotaController::class, 'resetPassword'])->name('anggota.reset-password')->middleware('permission:show-anggota');
    Route::get('anggota/{anggota}/dokumen/{dokumen}', [AnggotaController::class, 'dokumen'])->name('anggota.dokumen');
    Route::post('anggota/{anggota}/verifikasi-pc', [AnggotaController::class, 'verifyPc'])->name('anggota.verify-pc')->middleware('permission:verify-anggota-pc');
    Route::post('anggota/{anggota}/validasi-pd', [AnggotaController::class, 'validatePd'])->name('anggota.validate-pd')->middleware('permission:validate-anggota-pd');
    Route::post('anggota/{anggota}/persetujuan-pp', [AnggotaController::class, 'approvePp'])->name('anggota.approve-pp')->middleware('permission:approve-anggota-pp');
    Route::post('anggota/{anggota}/tolak', [AnggotaController::class, 'reject'])->name('anggota.reject');
});

require __DIR__.'/auth.php';
