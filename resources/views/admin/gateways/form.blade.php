@extends('layouts.admin')
@section('title', $gateway->exists ? 'Edit gateway configuration' : 'Add gateway configuration')
@section('page_title', 'Payment gateways')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">METADATA ONLY · NO CHECKOUT</span>
        <h1 style="margin-top:10px">{{ $gateway->exists ? 'Edit gateway configuration' : 'Add gateway configuration' }}</h1>
        <p>Save the display and amount metadata of a provider. No API keys, card data, deposits, or payouts are processed.</p>
    </div>
    <a class="cms-button" href="{{ route('admin.gateways.index') }}">Back to gateways</a>
</div>
<div class="cms-card">
    <form method="POST" action="{{ $gateway->exists ? route('admin.gateways.update', $gateway) : route('admin.gateways.store') }}">
        @csrf
        @if($gateway->exists) @method('PUT') @endif
        <div class="cms-form-grid">
            <div class="cms-field">
                <label for="gateway-name">Provider name *</label>
                <input id="gateway-name" name="name" maxlength="100" required value="{{ old('name', $gateway->name) }}" placeholder="Provider or internal payment method">
                @error('name')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label for="gateway-currency">ISO currency code *</label>
                <input id="gateway-currency" name="currency" minlength="3" maxlength="3" required value="{{ old('currency', $gateway->currency) }}" placeholder="EUR">
                @error('currency')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label for="gateway-symbol">Currency symbol *</label>
                <input id="gateway-symbol" name="symbol" maxlength="16" required value="{{ old('symbol', $gateway->symbol) }}" placeholder="€">
                @error('symbol')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label for="gateway-min">Minimum amount *</label>
                <input id="gateway-min" type="number" step="0.01" min="0.01" name="min_amount" required value="{{ old('min_amount', $gateway->min_amount) }}">
                @error('min_amount')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label for="gateway-max">Maximum amount *</label>
                <input id="gateway-max" type="number" step="0.01" min="0.01" name="max_amount" required value="{{ old('max_amount', $gateway->max_amount) }}">
                @error('max_amount')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label>Gateway status</label>
                <div class="cms-secret-hint">{{ $gateway->exists ? ($gateway->status ? 'Existing flag enabled (unchanged by edits)' : 'Inactive') : 'Inactive by default' }}</div>
            </div>
            <div class="cms-field">
                <label for="gateway-instructions">Notes / deposit instructions (admin record)</label>
                <textarea id="gateway-instructions" name="instruction" rows="4" maxlength="4000">{{ old('instruction', $gateway->instruction) }}</textarea>
                @error('instruction')<small role="alert">{{ $message }}</small>@enderror
            </div>
            <div class="cms-field">
                <label for="gateway-withdraw">Notes / withdrawal instructions (admin record)</label>
                <textarea id="gateway-withdraw" name="withdraw_instruction" rows="4" maxlength="4000">{{ old('withdraw_instruction', $gateway->withdraw_instruction) }}</textarea>
                @error('withdraw_instruction')<small role="alert">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="cms-secret-hint">Do not paste API credentials, passwords, card numbers or sensitive customer information here. This form does not issue or confirm payments.</div>
        <button type="submit" class="cms-button is-primary">Save gateway configuration</button>
    </form>
</div>
@endsection
