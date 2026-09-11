<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('gelar_depan')->nullable();
            $table->string('nama');
            $table->string('gelar_belakang')->nullable();
            $table->string('jenis_kelamin', 1);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('agama');
            $table->string('status_perkawinan');
            $table->string('foto_path')->nullable();

            $table->string('hp', 20);
            $table->string('whatsapp', 20);
            $table->string('email');
            $table->text('alamat');
            $table->string('provinsi_kode', 13);
            $table->string('kabupaten_kode', 13);
            $table->string('kecamatan_kode', 13);
            $table->string('kelurahan_kode', 13);
            $table->string('kode_pos', 10);

            $table->string('status_guru');
            $table->string('nip')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('nomor_gtk')->nullable();
            $table->string('mapel')->nullable();
            $table->string('jenjang');
            $table->string('nama_sekolah');
            $table->string('npsn', 20)->nullable();
            $table->string('status_sekolah');
            $table->text('alamat_sekolah');

            $table->string('nomor_anggota')->nullable()->unique();
            $table->date('tanggal_bergabung')->nullable();
            $table->string('pd_kode', 13);
            $table->string('pc_kode', 13);
            $table->string('status');
            $table->date('masa_berlaku_hingga')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['pd_kode', 'pc_kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
