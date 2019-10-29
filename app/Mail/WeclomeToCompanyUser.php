<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeclomeToCompanyUser extends Mailable
{
    use Queueable, SerializesModels;
    public $companyName;
    public $userName;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($companyName,$userName,$password)
    {
        $this->companyName = $companyName;
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "WELCOME To ".$this->companyName;
        return $this->subject($subject)->view('emails.company-users.welcome');
    }
}
