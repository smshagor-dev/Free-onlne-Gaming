@component('mail::message')
# 🔒 Verify Your Account

Hi {{ $user->name ?? 'User' }},

Welcome to **{{ config('app.name') }}**!  
To complete your registration, please use the verification code below:

@component('mail::panel', ['style' => 'background-color:#f0fdf4; border-left: 4px solid #10b981; padding: 16px; text-align:center;'])
## {{ $code }}
<span style="display:block; font-size:14px; color:#047857; margin-top:8px;">Expires in 10 minutes</span>
@endcomponent

Please enter this code to verify your account. ⚡  

@component('mail::button', ['url' => url('/verify'), 'color' => 'success'])
✅ Verify My Account
@endcomponent

If you did not create an account with us, please ignore this email.

Thanks,<br>
**{{ config('app.name') }} Team**
@endcomponent
