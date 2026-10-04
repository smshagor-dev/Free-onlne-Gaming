@component('mail::message')
{{-- Header --}}
<div style="text-align: center; padding: 20px 0;">
    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" style="width: 120px; margin-bottom: 20px;">
</div>

{{-- Greeting --}}
# 🎉 Congratulations, {{ $user->username }}!

{{-- Main Message --}}
<p style="font-size: 16px; color: #374151;">
    You have just received a <strong>VIP Bonus</strong> of <span style="color: #10B981;">{{ $user->currency }} {{ number_format($amount, 2) }}</span> in your account.
</p>

{{-- Info Box --}}
@component('mail::panel')
This bonus is available immediately. Make sure to check your dashboard for the latest updates on your VIP bonuses and rewards.
@endcomponent

{{-- Button --}}
@component('mail::button', ['url' => route('user.dashboard'), 'color' => 'success'])
View Your Dashboard
@endcomponent

{{-- Footer --}}
<p style="font-size: 14px; color: #6B7280; text-align: center; margin-top: 20px;">
    If you have any questions, please contact our support team.<br>
    Thank you for being a valued member of {{ config('app.name') }}.
</p>

@endcomponent
