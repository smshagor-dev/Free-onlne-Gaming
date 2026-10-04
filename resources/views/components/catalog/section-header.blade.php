@props(['title', 'subtitle' => null, 'href' => null, 'linkLabel' => 'View all'])

<div class="catalog-section-header">
    <div>
        <h2>{{ $title }}</h2>
        @if($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if($href)
        <a href="{{ $href }}" class="catalog-text-link">{{ $linkLabel }} <span aria-hidden="true">→</span></a>
    @endif
</div>
