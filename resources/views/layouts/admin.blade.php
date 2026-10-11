@extends('layouts.app')
@section('workspace-navigation')
<nav class="workspace-nav" aria-label="Admin navigation">
<strong>Administration</strong>
@foreach(['admin.dashboard'=>'Dashboard','admin.games.index'=>'Games','admin.games_categories.index'=>'Categories','admin.users.index'=>'Users','admin.lottaries.index'=>'Lottery','admin.pages.index'=>'Pages','admin.setting.edit'=>'Settings','admin.contacts.index'=>'Messages'] as $name=>$label)
<a href="{{ route($name) }}">{{ $label }}</a>
@endforeach
<form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit">Logout</button></form>
</nav>
@endsection
