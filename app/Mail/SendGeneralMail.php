<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendGeneralMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sapaan;
    public $nama;
    public $isi;
    public $link;
    public $penutup;

    /**
     * Buat instance baru.
     */
    public function __construct(string $sapaan, string $nama, string $isi, ?string $link = null, ?string $penutup = null)
    {
        $this->sapaan  = $sapaan;
        $this->nama    = $nama;
        $this->isi     = $isi;
        $this->link    = $link;
        $this->penutup = $penutup ?? 'Hormat kami,';
    }

    /**
     * Bangun pesan email.
     */
    public function build()
    {
        return $this->subject(config('app.name') . ' - Notifikasi')
                    ->markdown('emails.generalMail')
                    ->with([
                        'sapaan'  => $this->sapaan,
                        'nama'    => $this->nama,
                        'isi'     => $this->isi,
                        'link'    => $this->link,
                        'penutup' => $this->penutup,
                    ]);
    }
}
