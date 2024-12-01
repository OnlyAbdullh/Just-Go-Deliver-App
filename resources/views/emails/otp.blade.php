@component('mail::message')
    # Verify Your Action with OTP

    Hello,

    To proceed with your request, please use the following One-Time Password (OTP):

    **{{ $otp }}**

    This OTP is valid for the next **5 minutes**. If you didn’t request this code, no action is needed.

    Thank you for choosing {{ config('app.name') }}.

    Best regards,
    The {{ config('app.name') }} Team
@endcomponent
