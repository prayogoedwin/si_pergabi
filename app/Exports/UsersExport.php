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
    public function collection(): Collection
    {
        return User::query()->visibleTo(auth()->user())->with('roles')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Roles',
            'Email Verified At',
            'Created At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->email_verified_at?->format('Y-m-d H:i:s'),
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
