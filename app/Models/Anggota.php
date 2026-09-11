<?php

namespace App\Models;

use App\Helpers\Area;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    public const STATUS_BELUM_VERIFIKASI_EMAIL = 'belum_verifikasi_email';

    public const STATUS_MENUNGGU_VERIFIKASI_PC = 'menunggu_verifikasi_pc';

    public const STATUS_MENUNGGU_VALIDASI_PD = 'menunggu_validasi_pd';

    public const STATUS_MENUNGGU_PERSETUJUAN_PP = 'menunggu_persetujuan_pp';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    public const STATUS_DITOLAK = 'ditolak';

    public const JENIS_KELAMIN_L = 'L';

    public const JENIS_KELAMIN_P = 'P';

    protected $table = 'anggota';

    protected $fillable = [
        'user_id',
        'nik',
        'gelar_depan',
        'nama',
        'gelar_belakang',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'foto_path',
        'hp',
        'whatsapp',
        'email',
        'alamat',
        'provinsi_kode',
        'kabupaten_kode',
        'kecamatan_kode',
        'kelurahan_kode',
        'kode_pos',
        'status_guru',
        'nip',
        'nuptk',
        'nomor_gtk',
        'mapel',
        'jenjang',
        'nama_sekolah',
        'npsn',
        'status_sekolah',
        'alamat_sekolah',
        'nomor_anggota',
        'tanggal_bergabung',
        'pd_kode',
        'pc_kode',
        'status',
        'kanal_verifikasi',
        'masa_berlaku_hingga',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_bergabung' => 'date',
            'masa_berlaku_hingga' => 'date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_BELUM_VERIFIKASI_EMAIL => 'Belum verifikasi email',
            self::STATUS_MENUNGGU_VERIFIKASI_PC => 'Menunggu verifikasi PC',
            self::STATUS_MENUNGGU_VALIDASI_PD => 'Menunggu validasi PD',
            self::STATUS_MENUNGGU_PERSETUJUAN_PP => 'Menunggu persetujuan PP',
            self::STATUS_AKTIF => 'Aktif',
            self::STATUS_TIDAK_AKTIF => 'Tidak aktif',
            self::STATUS_DITOLAK => 'Ditolak',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenisKelaminOptions(): array
    {
        return [
            self::JENIS_KELAMIN_L => 'Laki-laki',
            self::JENIS_KELAMIN_P => 'Perempuan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function agamaOptions(): array
    {
        return [
            'Buddha' => 'Buddha',
            'Hindu' => 'Hindu',
            'Islam' => 'Islam',
            'Katolik' => 'Katolik',
            'Kristen' => 'Kristen',
            'Konghucu' => 'Konghucu',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusPerkawinanOptions(): array
    {
        return [
            'Belum kawin' => 'Belum kawin',
            'Kawin' => 'Kawin',
            'Cerai hidup' => 'Cerai hidup',
            'Cerai mati' => 'Cerai mati',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusGuruOptions(): array
    {
        return [
            'ASN' => 'ASN',
            'PPPK' => 'PPPK',
            'Honorer' => 'Honorer',
            'Guru Tetap Yayasan' => 'Guru Tetap Yayasan',
            'Guru Tidak Tetap' => 'Guru Tidak Tetap',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function jenjangOptions(): array
    {
        return [
            'TK' => 'TK',
            'SD' => 'SD',
            'SMP' => 'SMP',
            'SMA' => 'SMA',
            'SMK' => 'SMK',
            'Dhammasekha' => 'Dhammasekha',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusSekolahOptions(): array
    {
        return [
            'Negeri' => 'Negeri',
            'Swasta' => 'Swasta',
        ];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return array_keys(self::statusLabels());
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_BELUM_VERIFIKASI_EMAIL && $this->kanal_verifikasi === 'whatsapp') {
            return 'Belum verifikasi WhatsApp';
        }

        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function namaLengkap(): string
    {
        return trim(implode(' ', array_filter([
            $this->gelar_depan,
            $this->nama,
            $this->gelar_belakang,
        ])));
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function canAccessKartuDigital(): bool
    {
        return $this->isAktif() && filled($this->nomor_anggota);
    }

    /**
     * @return list<array{key: string, label: string, done: bool, current: bool}>
     */
    public function portalSteps(): array
    {
        $order = [
            self::STATUS_BELUM_VERIFIKASI_EMAIL => 'Akun',
            self::STATUS_MENUNGGU_VERIFIKASI_PC => 'PC',
            self::STATUS_MENUNGGU_VALIDASI_PD => 'PD',
            self::STATUS_MENUNGGU_PERSETUJUAN_PP => 'PP',
            self::STATUS_AKTIF => 'Aktif',
        ];

        if (in_array($this->status, [self::STATUS_DITOLAK, self::STATUS_TIDAK_AKTIF], true)) {
            return [];
        }

        $keys = array_keys($order);
        $currentIndex = array_search($this->status, $keys, true);
        $currentIndex = $currentIndex === false ? 0 : (int) $currentIndex;

        $steps = [];

        foreach ($order as $key => $label) {
            $index = array_search($key, $keys, true);
            $steps[] = [
                'key' => $key,
                'label' => $label,
                'done' => $index < $currentIndex || $this->status === self::STATUS_AKTIF,
                'current' => $index === $currentIndex && $this->status !== self::STATUS_AKTIF,
            ];
        }

        return $steps;
    }

    public function jenisKelaminLabel(): string
    {
        return self::jenisKelaminOptions()[$this->jenis_kelamin] ?? $this->jenis_kelamin;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(AnggotaDokumen::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(AnggotaStatusLog::class)->orderByDesc('id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_kode', 'kode');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kabupaten_kode', 'kode');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kecamatan_kode', 'kode');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kelurahan_kode', 'kode');
    }

    public function pd(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'pd_kode', 'kode');
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'pc_kode', 'kode');
    }

    /**
     * @param  Builder<Anggota>  $query
     * @return Builder<Anggota>
     */
    public function scopeVisibleTo(Builder $query, User $viewer, ?string $provinsi = null, ?string $kabupaten = null): Builder
    {
        $provinsi = filled($provinsi) ? $provinsi : null;
        $kabupaten = filled($kabupaten) ? $kabupaten : null;

        if ($kabupaten && $provinsi && ! str_starts_with($kabupaten, $provinsi.'.')) {
            $kabupaten = null;
        }

        return match ($viewer->organisasiLevel()) {
            Area::CABANG => filled($viewer->pc_kode)
                ? $query->where('pc_kode', $viewer->pc_kode)
                : $query->whereRaw('0 = 1'),
            Area::DAERAH => filled($viewer->pd_kode)
                ? $query->where('pd_kode', $viewer->pd_kode)
                    ->when(
                        $kabupaten && str_starts_with($kabupaten, $viewer->pd_kode.'.'),
                        fn (Builder $scoped) => $scoped->where('pc_kode', $kabupaten)
                    )
                : $query->whereRaw('0 = 1'),
            default => $query
                ->when($kabupaten, fn (Builder $scoped) => $scoped->where('pc_kode', $kabupaten))
                ->when($provinsi && ! $kabupaten, fn (Builder $scoped) => $scoped->where('pd_kode', $provinsi)),
        };
    }
}
