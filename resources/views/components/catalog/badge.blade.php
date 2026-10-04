@props(['tone' => 'neutral'])

<span {{ $attributes->class(['catalog-badge', 'catalog-badge--'.$tone]) }}>
    {{ $slot }}
</span>
