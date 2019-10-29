<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimeoffRequestToCompany extends Mailable
{
    use Queueable, SerializesModels;
    public $startDate;
    public $endDate;
    public $days;
    public $employeeName;
    public $policy;
    public $reason;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($startDate,$endDate, $days,$employeeName, $policy, $reason)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->days = $days;
        $this->employeeName = $employeeName;
        $this->policy = $policy;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "TIME OFF REQUEST TO COMPANY";
        return $this->subject($subject)->view('emails.timeoffs.request');
    }
}
