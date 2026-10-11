@foreach($games as $game)
    @include('components.home-game-card', ['game' => $game, 'fallbackTag' => 'Free', 'cardsOnly' => true])
@endforeach
