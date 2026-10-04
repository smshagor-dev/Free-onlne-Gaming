@extends('catalog.layout')
@section('content')
<div class="catalog-shell"><section class="catalog-section"><x-catalog.section-header title="Gaming Preferences" subtitle="Tune recommendations without changing your security/profile settings." :href="route('user.profile.edit')" />
<form method="POST" action="{{ route('catalog.user.settings.update') }}" class="preference-form">@csrf @method('PUT')
<fieldset><legend>Preferred genres</legend><div class="preference-grid">@forelse($genres as $genre) @php($value=$genre['name']) <label><input type="checkbox" name="preferred_genres[]" value="{{ $value }}" @checked(in_array($value, $preferences->preferred_genres ?? [], true))> {{ $value }}</label>@empty<p>Genre metadata is temporarily unavailable.</p>@endforelse</div></fieldset>
<fieldset><legend>Preferred platforms</legend><div class="preference-grid">@forelse($platforms as $platform) @php($value=$platform['name']) <label><input type="checkbox" name="preferred_platforms[]" value="{{ $value }}" @checked(in_array($value, $preferences->preferred_platforms ?? [], true))> {{ $value }}</label>@empty<p>Platform metadata is temporarily unavailable.</p>@endforelse</div></fieldset>
<button type="submit">Save preferences</button></form>
<div class="catalog-settings-links"><a href="{{ route('user.profile.edit') }}">Edit profile information</a><a href="{{ route('catalog.user.library') }}">Saved games & history</a></div>
</section></div>
@endsection
