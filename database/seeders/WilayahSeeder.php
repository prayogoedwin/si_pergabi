<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            $this->seedSample();

            return;
        }

        $path = database_path('dump/wilayah.sql');

        if (! is_file($path)) {
            $this->command?->warn("Wilayah dump not found: {$path}");

            return;
        }

        DB::table('wilayah')->truncate();
        DB::connection()->disableQueryLog();

        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Unable to read wilayah dump: {$path}");
        }

        $rows = [];
        $imported = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                if (! preg_match("/^\s*\('((?:\\\\'|[^'])*)','((?:\\\\'|[^'])*)'\)[,;]?\s*$/", $line, $match)) {
                    continue;
                }

                $rows[] = [
                    'kode' => $this->unescape($match[1]),
                    'nama' => $this->unescape($match[2]),
                ];

                if (count($rows) >= 1000) {
                    DB::table('wilayah')->insert($rows);
                    $imported += count($rows);
                    $rows = [];
                }
            }

            if ($rows !== []) {
                DB::table('wilayah')->insert($rows);
                $imported += count($rows);
            }
        } finally {
            fclose($handle);
        }

        $this->command?->info("Imported {$imported} wilayah rows.");
    }

    private function seedSample(): void
    {
        Wilayah::query()->insert([
            ['kode' => '11', 'nama' => 'Aceh'],
            ['kode' => '36', 'nama' => 'Banten'],
            ['kode' => '36.71', 'nama' => 'Kota Tangerang'],
            ['kode' => '36.71.01', 'nama' => 'Tangerang'],
            ['kode' => '36.71.01.1001', 'nama' => 'Sukasari'],
        ]);
    }

    private function unescape(string $value): string
    {
        return str_replace(["\\'", '\\\\'], ["'", '\\'], $value);
    }
}
