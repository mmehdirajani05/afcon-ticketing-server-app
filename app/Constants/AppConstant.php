<?php

namespace App\Constants;

class AppConstant
{
    // Replace value with random generator once mail is configured
    const OTP_HARDCODED  = '1234';
    const OTP_EXPIRY_MINUTES = 10;

    const OTP_TYPE_EMAIL_VERIFICATION = 'email_verification';
    const OTP_TYPE_PASSWORD_RESET     = 'password_reset';
}
