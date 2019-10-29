<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruitCreate extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $home_address;
    public $personal_email;
    public $mobile_phone_number;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name,$last_name,$home_address,$personal_email,$mobile_phone_number)
    {
        $this->name = $first_name." ".$last_name;
        $this->home_address = $home_address;
        $this->personal_email = $personal_email;
        $this->mobile_phone_number = $mobile_phone_number;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "new work application";
        return $this->subject($subject)->view('emails.recruits.create');
    }
}
