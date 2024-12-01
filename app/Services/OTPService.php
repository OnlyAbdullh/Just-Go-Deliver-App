<?php
namespace App\Services;

use App\Repositories\OTPRepository;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;

class OTPService
{
    protected $otpRepository;

    public function __construct(OTPRepository $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    public function sendOTP(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $otp = random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(5);

        $this->otpRepository->store($user->id, $otp, $expiresAt);

        Mail::to($user->email)->send(new OTPMail($otp));
    }

    public function validateOTP(string $email, string $inputOtp): bool
    {
        $user = User::where('email', $email)->firstOrFail();

        $storedOtp = $this->otpRepository->getValidOTP($user->id);

        return $storedOtp && $storedOtp->otp === $inputOtp;
    }
}
