@props([
    'title',
    'message' => null,
    'type' => 'empty',
    'actionHref' => null,
    'actionLabel' => null,
])

<section {{ $attributes->class(['catalog-state', 'catalog-state--'.$type]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <div class="catalog-state__icon" aria-hidden="true">
        {{ $type === 'error' ? '!' : '⌁' }}
    </div>
    <h2>{{ $title }}</h2>
    @if($message)
        <p>{{ $message }}</p>
    @endif
    @if($actionHref && $actionLabel)
        <a class="catalog-button catalog-button--secondary" href="{{ $actionHref }}">{{ $actionLabel }}</a>
    @endif
</section>
