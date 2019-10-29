<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProformaToCompany extends Mailable
{
    use Queueable, SerializesModels;
    public $companyUserName;
    public $companyName;
    public $startDate;
    public $endDate;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($companyName,$companyUserName,$startDate,$endDate)
    {
        $this->companyUserName = $companyUserName;
        $this->companyName = $companyName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "PROFORMA TO $this->companyName";
        return $this->subject($subject)->view('emails.invoices.proforma');
    }
}
