@extends('layouts.admin')
@section('content')
<h1>{{ $user->name }}</h1><p>{{ $user->email }}</p><p>Username: {{ $user->username }}</p>
<p>Status: {{ $user->is_banned ? 'Banned' : 'Active' }}</p>
<a href="{{ route('admin.users.edit', $user->id) }}">Edit account</a>
@endsection
