@foreach($games as $game)
    <a href="{{ route('playable.play', $game['id']) }}" class="block bg-slate-800 overflow-hidden rounded border border-slate-700 hover:border-blue-400">
        @if($game['image'])
            <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full aspect-[4/3] object-cover" loading="lazy">
        @endif
        <span class="block px-3 py-2 text-sm font-medium text-white truncate">{{ $game['name'] }}</span>
    </a>
@endforeach
