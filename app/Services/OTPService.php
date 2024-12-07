<?php

namespace App\Services;

use App\Jobs\SendOtpEmailJob;
use Ichtrojan\Otp\Otp;

class OTPService
{
    public function __construct( )
    {
    }

    public function sendOTP(string $email): void
    {
        $otpDetails = (new Otp)->generate($email, 'numeric', 6, 5);
        SendOtpEmailJob::dispatch($email, $otpDetails->token);
    }


    public function validateOTP(string $inputOtp, string $email): array
    {
        $validationResult = (new Otp)->validate($email, $inputOtp);

        if (!$validationResult->status) {
            return [
                'successful' => false,
                'message' => $validationResult->message,
                'status_code' => 400,
            ];
        }

        return [
            'successful' => true,
            'message' => 'OTP is valid.',
            'status_code' => 200,
        ];
    }

}
