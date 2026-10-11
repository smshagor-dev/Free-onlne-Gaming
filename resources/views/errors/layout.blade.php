@extends('layouts.app')
@section('content')
<section class="error-panel">
<div class="code">@yield('code')</div><h1>@yield('heading')</h1><p>@yield('message')</p>
<div class="actions"><a href="{{ route('catalog.index') }}">Game catalog</a><a href="{{ url('/') }}">Home</a></div>
</section>
@endsection
