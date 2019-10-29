<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentToMember extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $hours;
    public $sales;
    public $period;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$hours,$sales,$period)
    {
        $this->name = $name;
        $this->hours = $hours;
        $this->sales = $sales;
        $this->period = $period;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "PAYMENT TO ".$this->name;
        return $this->subject($subject)->view('emails.employees.payment');
    }
}
