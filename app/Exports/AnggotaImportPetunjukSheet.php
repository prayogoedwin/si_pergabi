<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnggotaImportPetunjukSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['Kolom', 'Keterangan'];
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        return [
            ['NTA', 'Wajib. Nomor anggota lama, format TAHUN.KODE_PROV.KODE_KAB.URUT. Contoh 2024.52.5208.002. Provinsi dan kabupaten/kota (PD/PC) diambil otomatis dari NTA.'],
            ['NAMA', 'Wajib. Gelar belakang boleh ditulis setelah koma, contoh: Nopiyanti, S.Pd'],
            ['HP', 'Opsional. Contoh 081234567890. Jika kosong, sistem mengisi 081 agar kolom database terisi.'],
            ['EMAIL', 'Wajib. Dipakai sebagai akun login. Harus unik.'],
            ['TEMPAT LAHIR', 'Wajib.'],
            ['JL', 'Wajib. Laki-laki / Perempuan, atau L / P'],
            ['ALAMAT', 'Wajib. Alamat lengkap.'],
            ['NIK', 'Opsional. 16 digit. Jika kosong, sistem mengisi sementara agar akun bisa dibuat; anggota bisa memperbarui NIK di profil.'],
            ['PD / PC', 'Tidak perlu kolom terpisah. 2024.52.5208.002 = Provinsi 52 (NTB), Kabupaten 52.08 (Lombok Utara).'],
            ['Masa aktif', 'Setiap baris impor dihitung pendaftar baru. Tanggal bergabung hari ini, masa berlaku 5 tahun ke depan. Nomor urut KTA berikutnya menyambung dari NTA yang sudah diimpor di kabupaten/kota yang sama.'],
        ];
    }

    public function title(): string
    {
        return 'Petunjuk';
    }
}
