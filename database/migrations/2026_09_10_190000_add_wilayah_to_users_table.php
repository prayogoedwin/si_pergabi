<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pd_kode', 13)->nullable();
            $table->string('pc_kode', 13)->nullable();
            $table->index(['pd_kode', 'pc_kode']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pd_kode', 'pc_kode']);
            $table->dropColumn(['pd_kode', 'pc_kode']);
        });
    }
};
