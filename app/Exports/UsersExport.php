<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements Export, FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly ?int $roleId = null) {}

    public function collection(): Collection
    {
        return User::query()
            ->visibleTo(auth()->user())
            ->with(['roles', 'pd', 'pc', 'anggota.pd', 'anggota.pc'])
            ->when($this->roleId, fn ($query) => $query->whereHas('roles', fn ($roles) => $roles->whereKey($this->roleId)))
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Roles',
            'Provinsi',
            'Kota',
            'Email Verified At',
            'Created At',
        ];
    }

    public function map($user): array
    {
        $wilayah = $user->wilayahTampil();

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $wilayah['provinsi'],
            $wilayah['kota'],
            $user->email_verified_at?->format('Y-m-d H:i:s'),
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
