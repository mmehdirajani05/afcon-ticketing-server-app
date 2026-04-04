<?php

namespace App\Exceptions;

use Exception;

class EmailNotVerifiedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Email not verified. A new OTP has been sent to your email.');
    }
}
