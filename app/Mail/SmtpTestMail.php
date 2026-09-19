<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $mailerName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Uji SMTP SI PERGABI',
        );
    }

    public function content(): Content
    {
        $mailer = e($this->mailerName);
        $waktu = e(now()->timezone(config('app.timezone'))->format('d-m-Y H:i:s'));

        return new Content(
            htmlString: (string) new HtmlString(
                '<p>Ini email uji SMTP dari SI PERGABI.</p>'.
                '<p>Jika Anda menerima pesan ini, pengaturan email sudah berjalan.</p>'.
                "<p>Mailer: {$mailer}<br>Waktu: {$waktu}</p>"
            ),
        );
    }
}
