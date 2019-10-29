<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitApproved extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $password;
    public $link;
    public $email;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$password, $link,$email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "You approved for Castellum Pro";
        return $this->subject($subject)->view('emails.employees.approved');
    }
}
