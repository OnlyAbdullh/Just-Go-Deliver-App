<?php

namespace App\Repositories;

use App\Models\OTP;
use App\Repositories\Contracts\OTPRepositoryInterface;
use Carbon\Carbon;

class OTPRepository implements OTPRepositoryInterface
{
    public function store(int $userId, string $otp, Carbon $expiresAt): void
    {
        OTP::create([
            'user_id' => $userId,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
    }

    public function getValidOTP(int $userId): ?OTP
    {
        return OTP::where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();
    }
}
