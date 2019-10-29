<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceToCompany extends Mailable
{
    use Queueable, SerializesModels;
    public $companyUserName;
    public $companyName;
    public $invoiceId;
    public $total;
    public $period;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($companyName,$companyUserName,$invoiceId,$total, $period)
    {
        $this->companyUserName = $companyUserName;
        $this->companyName = $companyName;
        $this->invoiceId = $invoiceId;
        $this->total = $total;
        $this->period = $period;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "INVOICE TO $this->companyName";
        return $this->subject($subject)->view('emails.invoices.company');
    }
}
