<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $grants = [
            'ketua-pc' => 'verify-anggota-pc',
            'ketua-pd' => 'validate-anggota-pd',
            'ketua-umum-pp' => 'approve-anggota-pp',
        ];

        foreach ($grants as $slug => $permissionName) {
            $role = Role::query()->where('slug', $slug)->first();

            if ($role === null) {
                continue;
            }

            $permission = Permission::query()->firstOrCreate(['name' => $permissionName]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    public function down(): void
    {
        $grants = [
            'ketua-pc' => 'verify-anggota-pc',
            'ketua-pd' => 'validate-anggota-pd',
            'ketua-umum-pp' => 'approve-anggota-pp',
        ];

        foreach ($grants as $slug => $permissionName) {
            $role = Role::query()->where('slug', $slug)->first();
            $permission = Permission::query()->where('name', $permissionName)->first();

            if ($role !== null && $permission !== null) {
                $role->permissions()->detach($permission->id);
            }
        }
    }
};
