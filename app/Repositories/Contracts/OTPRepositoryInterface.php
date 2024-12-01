<?php
namespace App\Repositories\Contracts;
use App\Models\OTP;
use Carbon\Carbon;

interface OTPRepositoryInterface
{
    public function store(int $userId, string $otp, Carbon $expiresAt): void;

    public function getValidOTP(int $userId): ?OTP;
}
