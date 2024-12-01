@component('mail::message')
    # Your One-Time Password (OTP)

    Use the following OTP to complete your action:

    **{{ $otp }}**

    This OTP will expire in 10 minutes.

    If you did not request this, please ignore this email.

    Thanks,
    {{ config('app.name') }}
@endcomponent
