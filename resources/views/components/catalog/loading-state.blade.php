<div class="catalog-loading-state" aria-live="polite" aria-busy="true">
    <span class="catalog-spinner" aria-hidden="true"></span>
    <span>{{ $slot->isEmpty() ? 'Loading games…' : $slot }}</span>
</div>
