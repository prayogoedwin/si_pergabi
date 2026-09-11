<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->string('jenis');
            $table->string('path');
            $table->string('nama_asli');
            $table->string('mime')->nullable();
            $table->unsignedInteger('ukuran')->default(0);
            $table->timestamps();

            $table->index(['anggota_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_dokumen');
    }
};
