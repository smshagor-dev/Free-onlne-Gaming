@extends('layouts.app')
@section('content')
<section class="account-panel">
<h1>Welcome, {{ $userInfo->name }}</h1>
<p>Discover free games, save your favorites and keep track of your lottery tickets.</p>
<div class="portal-grid">
<a href="{{ route('playable.index') }}">Play Free Games</a>
<a href="{{ route('user.games.viewFavorites') }}">My Favorites</a>
<a href="{{ route('catalog.user.library') }}">My Library</a>
<a href="{{ route('user.games.lastplay') }}">Recently Played</a>
<a href="{{ route('user.lottaries.my') }}">My Lottery Tickets</a>
<a href="{{ route('user.lottaries.mywin') }}">Lottery Wins</a>
<a href="{{ route('user.profile') }}">Edit Profile</a>
</div>
</section>
@endsection
