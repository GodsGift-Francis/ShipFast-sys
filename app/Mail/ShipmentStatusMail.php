<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Shipment $shipment,
        public string $subjectLine,
        public string $bodyLine,
    ) {}

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.shipment-status');
    }
}
