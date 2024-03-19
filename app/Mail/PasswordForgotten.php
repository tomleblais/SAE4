<?php

namespace App\Mail;

use App\Models\Personne;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordForgotten extends Mailable
{
    use Queueable, SerializesModels;

    public Personne $user;

    /**
     * Create a new message instance.
     *
     * @param Personne $user
     */
    public function __construct(Personne $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): PasswordForgotten
    {
        return $this->view('connection/forgottenPassword');
    }
}
