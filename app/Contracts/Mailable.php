<?php

namespace App\Contracts;

abstract class Mailable
{
    public $name;
    public $email;

    public function __construct($email, $name = '')
    {
        $this->email = $email;
        $this->name = $name;
    }
}
