<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaDokumen extends Model
{
    public const PAS_FOTO = 'pas_foto';

    public const KTP = 'ktp';

    public const SK_MENGAJAR = 'sk_mengajar';

    public const BUKTI_PEMBAYARAN = 'bukti_pembayaran';

    public const IJAZAH = 'ijazah';

    public const SERTIFIKAT_PENDIDIK = 'sertifikat_pendidik';

    protected $table = 'anggota_dokumen';

    protected $fillable = [
        'anggota_id',
        'jenis',
        'path',
        'nama_asli',
        'mime',
        'ukuran',
    ];

    /**
     * @return array<string, string>
     */
    public static function jenisLabels(): array
    {
        return [
            self::PAS_FOTO => 'Pas foto',
            self::SK_MENGAJAR => 'SK Mengajar',
            self::BUKTI_PEMBAYARAN => 'Bukti pembayaran',
            self::KTP => 'KTP',
            self::IJAZAH => 'Ijazah',
            self::SERTIFIKAT_PENDIDIK => 'Sertifikat Pendidik',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function formJenisLabels(): array
    {
        return [
            self::PAS_FOTO => 'Pas foto',
            self::SK_MENGAJAR => 'SK Mengajar',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function displayJenisLabels(): array
    {
        return [
            self::PAS_FOTO => 'Pas foto',
            self::SK_MENGAJAR => 'SK Mengajar',
            self::BUKTI_PEMBAYARAN => 'Bukti pembayaran',
        ];
    }

    /**
     * @return list<string>
     */
    public static function requiredJenis(): array
    {
        return [
            self::PAS_FOTO,
            self::SK_MENGAJAR,
        ];
    }

    public function jenisLabel(): string
    {
        return self::jenisLabels()[$this->jenis] ?? $this->jenis;
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
