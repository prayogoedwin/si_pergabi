<?php

namespace App\Exports;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnggotaExport implements Export, FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly ?string $provinsi,
        private readonly ?string $kabupaten,
        private readonly ?string $status,
        private readonly ?string $nama,
    ) {}

    public function collection(): Collection
    {
        return Anggota::query()
            ->visibleTo($this->user, $this->provinsi, $this->kabupaten)
            ->with(['pd', 'pc', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan', 'dokumen'])
            ->when(filled($this->status), fn ($query) => $query->where('status', $this->status))
            ->cariNama($this->nama)
            ->latest()
            ->get();
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nomor anggota',
            'NIK',
            'Gelar depan',
            'Nama',
            'Gelar belakang',
            'Jenis kelamin',
            'Tempat lahir',
            'Tanggal lahir',
            'Agama',
            'Status perkawinan',
            'HP',
            'WhatsApp',
            'Email',
            'Alamat',
            'Kode provinsi',
            'Provinsi',
            'Kode kabupaten/kota',
            'Kabupaten/kota',
            'Kode kecamatan',
            'Kecamatan',
            'Kode kelurahan',
            'Kelurahan',
            'Kode pos',
            'Status guru',
            'NIP',
            'NUPTK',
            'Nomor GTK',
            'Mata pelajaran',
            'Jenjang',
            'Nama sekolah',
            'NPSN',
            'Status sekolah',
            'Alamat sekolah',
            'Tanggal bergabung',
            'Kode PD',
            'PD',
            'Kode PC',
            'PC',
            'Status',
            'Kanal verifikasi',
            'Masa berlaku hingga',
            'Path foto',
            'Path pas foto',
            'Path KTP',
            'Path SK mengajar',
            'Path ijazah',
            'Path sertifikat pendidik',
            'Path bukti pembayaran',
            'Dibuat pada',
        ];
    }

    /**
     * @param  Anggota  $anggota
     * @return list<int|string|null>
     */
    public function map($anggota): array
    {
        return [
            $anggota->id,
            $anggota->nomor_anggota,
            $anggota->nik,
            $anggota->gelar_depan,
            $anggota->nama,
            $anggota->gelar_belakang,
            $anggota->jenisKelaminLabel(),
            $anggota->tempat_lahir,
            $anggota->tanggal_lahir?->format('Y-m-d'),
            $anggota->agama,
            $anggota->status_perkawinan,
            $anggota->hp,
            $anggota->whatsapp,
            $anggota->email,
            $anggota->alamat,
            $anggota->provinsi_kode,
            $anggota->provinsi?->nama,
            $anggota->kabupaten_kode,
            $anggota->kabupaten?->nama,
            $anggota->kecamatan_kode,
            $anggota->kecamatan?->nama,
            $anggota->kelurahan_kode,
            $anggota->kelurahan?->nama,
            $anggota->kode_pos,
            $anggota->status_guru,
            $anggota->nip,
            $anggota->nuptk,
            $anggota->nomor_gtk,
            $anggota->mapel,
            $anggota->jenjang,
            $anggota->nama_sekolah,
            $anggota->npsn,
            $anggota->status_sekolah,
            $anggota->alamat_sekolah,
            $anggota->tanggal_bergabung?->format('Y-m-d'),
            $anggota->pd_kode,
            $anggota->pd?->nama,
            $anggota->pc_kode,
            $anggota->pc?->nama,
            $anggota->statusLabel(),
            $anggota->kanal_verifikasi,
            $anggota->masa_berlaku_hingga?->format('Y-m-d'),
            $anggota->foto_path,
            $this->documentPaths($anggota, AnggotaDokumen::PAS_FOTO),
            $this->documentPaths($anggota, AnggotaDokumen::KTP),
            $this->documentPaths($anggota, AnggotaDokumen::SK_MENGAJAR),
            $this->documentPaths($anggota, AnggotaDokumen::IJAZAH),
            $this->documentPaths($anggota, AnggotaDokumen::SERTIFIKAT_PENDIDIK),
            $this->documentPaths($anggota, AnggotaDokumen::BUKTI_PEMBAYARAN),
            $anggota->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function documentPaths(Anggota $anggota, string $jenis): string
    {
        return $anggota->dokumen
            ->where('jenis', $jenis)
            ->sortByDesc('id')
            ->pluck('path')
            ->filter(fn ($path) => filled($path))
            ->implode(' | ');
    }
}
