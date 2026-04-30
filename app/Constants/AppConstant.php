<?php

namespace App\Constants;

class AppConstant
{
    // OTP
    const OTP_EXPIRY_MINUTES           = 10;
    const OTP_DIGITS                   = 6;

    const OTP_TYPE_EMAIL_VERIFICATION  = 'email_verification';
    const OTP_TYPE_PASSWORD_RESET      = 'password_reset';
    const OTP_TYPE_LOGIN               = 'login';

    // Fan ID
    const FAN_ID_PREFIX                = 'AFCON27';
    const FAN_ID_COUNTRY               = 'TZ';

    const FAN_ID_STATUS_PENDING        = 'pending';
    const FAN_ID_STATUS_VERIFIED       = 'verified';
    const FAN_ID_STATUS_REJECTED       = 'rejected';

    const IDENTITY_TYPE_NIC            = 'nic';           // Tanzanian citizen — National ID Card
    const IDENTITY_TYPE_RP             = 'rp';            // Tanzania resident — Resident Permit
    const IDENTITY_TYPE_FN             = 'fn';            // Foreign National — passport holder
    const IDENTITY_TYPE_SPECIAL_PASS   = 'special_pass';  // Diplomat, media, official

    // Immigration
    const IMMIGRATION_MODE_REALTIME    = 'realtime';
    const IMMIGRATION_MODE_DELAYED     = 'delayed';

    // Payment & Booking
    const CURRENCY_TZS                 = 'TZS';

    const PAYMENT_STATUS_PENDING       = 'pending';
    const PAYMENT_STATUS_PAID          = 'paid';
    const PAYMENT_STATUS_FAILED        = 'failed';

    const BOOKING_STATUS_PENDING       = 'pending';
    const BOOKING_STATUS_CONFIRMED     = 'confirmed';
    const BOOKING_STATUS_CANCELLED     = 'cancelled';
    const BOOKING_STATUS_REFUNDED      = 'refunded';

    const REFUND_STATUS_REQUESTED      = 'refund_requested';
    const REFUND_STATUS_PROCESSING     = 'refund_processing';
    const REFUND_STATUS_REFUNDED       = 'refunded';
    const REFUND_STATUS_FAILED         = 'failed';

    // Social providers
    const SOCIAL_PROVIDER_GOOGLE       = 'google';
    const SOCIAL_PROVIDER_APPLE        = 'apple';

    // Device platforms
    const PLATFORM_ANDROID             = 'android';
    const PLATFORM_IOS                 = 'ios';
    const PLATFORM_WEB                 = 'web';

    // User roles
    const ROLE_CUSTOMER                = 'customer';
    const ROLE_ADMIN                   = 'admin';
    const ROLE_SUB_ADMIN               = 'sub_admin';

    // Registration sources
    const SOURCE_EMAIL                 = 'email';
    const SOURCE_GOOGLE                = 'google';
    const SOURCE_APPLE                 = 'apple';
}
