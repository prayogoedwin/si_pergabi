<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('anggota')->where('jenjang', 'TK')->update(['jenjang' => 'TK/PAUD']);
    }

    public function down(): void
    {
        DB::table('anggota')->where('jenjang', 'TK/PAUD')->update(['jenjang' => 'TK']);
    }
};
