<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyStatsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $moderator;

    public function __construct(array $data, $moderator = null)
    {
        $this->data = $data;
        $this->moderator = $moderator;
    }

    public function build()
    {
        $dateOrTimestamp = $this->data['date'] ?? $this->data['timestamp'] ?? now()->toDateString();

        return $this->subject('Статистика сайта — ' . $dateOrTimestamp)
                    ->view('emails.daily_stats')
                    ->with([
                        'data' => $this->data,
                        'moderator' => $this->moderator,
                    ]);
    }

}
