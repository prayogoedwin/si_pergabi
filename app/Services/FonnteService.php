<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FonnteService
{
    public function __construct(private readonly SettingService $settings) {}

    public function send(string $target, string $message): void
    {
        $token = $this->settings->fonnteToken();

        if (! filled($token)) {
            throw new RuntimeException('Token Fonnte belum diatur.');
        }

        $response = Http::asForm()
            ->withHeaders(['Authorization' => $token])
            ->post('https://api.fonnte.com/send', [
                'target' => $this->normalize($target),
                'message' => $message,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal mengirim WhatsApp melalui Fonnte.');
        }

        $status = $response->json('status');

        if ($status === false || $status === 'false') {
            throw new RuntimeException((string) ($response->json('reason') ?: 'Fonnte menolak pengiriman.'));
        }
    }

    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }
}
