@extends('layouts.admin')
@section('content')
<h1 class="text-3xl font-bold mb-6">Free Games Dashboard</h1>
<div class="portal-grid">
<a href="{{ route('admin.users.index') }}">Users: {{ $users_count }}</a>
<a href="{{ route('admin.games.index') }}">Manage Free Games</a>
<a href="{{ route('admin.games_categories.index') }}">Manage Categories</a>
<a href="{{ route('admin.lottaries.index') }}">Manage Lottery</a>
<a href="{{ route('admin.contacts.index') }}">Contact Messages</a>
</div>
@endsection
