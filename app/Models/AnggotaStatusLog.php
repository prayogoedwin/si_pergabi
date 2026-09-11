<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaStatusLog extends Model
{
    public $timestamps = false;

    protected $table = 'anggota_status_log';

    protected $fillable = [
        'anggota_id',
        'status_dari',
        'status_ke',
        'alasan',
        'user_id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function statusDariLabel(): string
    {
        if ($this->status_dari === null) {
            return '—';
        }

        return Anggota::statusLabels()[$this->status_dari] ?? $this->status_dari;
    }

    public function statusKeLabel(): string
    {
        return Anggota::statusLabels()[$this->status_ke] ?? $this->status_ke;
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
