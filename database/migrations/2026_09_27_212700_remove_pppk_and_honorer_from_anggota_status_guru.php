<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('anggota')->whereIn('status_guru', ['PPPK', 'P3K'])->update(['status_guru' => 'ASN']);
        DB::table('anggota')->where('status_guru', 'Honorer')->update(['status_guru' => 'Guru Tidak Tetap']);
    }

    public function down(): void
    {
        // Mapping is many-to-one; original PPPK/Honorer values cannot be restored.
    }
};
