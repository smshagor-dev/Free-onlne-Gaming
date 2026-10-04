@extends('layouts.app')

@section('content')
<div style="width:100%;padding:20px;background-color:#111827;box-sizing:border-box;">
    <div style="max-width:1400px;margin:0 auto;">
        <!-- Header -->
        <div style="display:flex;justify-content:start;align-items:center;flex-wrap:wrap;margin-bottom:25px;gap:10px;">
            <i class="fas fa-gamepad" style="color:green; font-size:24px;"></i>
            <h1 style="font-size:28px;font-weight:600;color:#fff;display:flex;align-items:center;gap:10px;margin:0;">
                New Comming
                <span style="background-color:green;color:white;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">Recent Added</span>
            </h1>
        </div>

        <!-- Games Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;width:100%;">
            @foreach($games as $casino)
                <div class="game-card" style="position:relative;border-radius:10px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.1);transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;">
                    
                    <!-- Trending Badge -->
                    <div style="position:absolute;top:10px;left:10px;background-color:green;color:white;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:600;z-index:10;">
                        New #{{ $loop->iteration }}
                    </div>
                    
                    <!-- Game Image -->
                    <img src="{{ $casino->img }}" alt="{{ $casino->name }}" style="width:100%;height:160px;object-fit:cover;display:block;">
                    
                    <!-- Overlay -->
                    <div class="overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s;">
                        <div style="color:white;font-weight:600;font-size:14px;margin-bottom:10px;text-align:center;">
                            {{ $casino->name }}
                        </div>
                        <div style="display:flex;gap:10px;">
                            <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}"
                               target="_blank"
                               style="padding:8px 16px;background-color:#22c55e;color:black;border-radius:9999px;font-size:14px;font-weight:500;text-decoration:none;">
                               Play
                            </a>
                            <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}"
                               target="_blank"
                               style="padding:8px 16px;background-color:#facc15;color:black;border-radius:9999px;font-size:14px;font-weight:500;text-decoration:none;">
                               Demo
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="padding:10px;background-color:#1f2937;color:white;text-align:center;">
                        <div style="font-weight:600;font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $casino->name }}
                        </div>
                        @if(isset($casino->categories))
                            <div style="font-size:12px;color:#d1d5db;margin-top:2px;">
                                {{ $casino->categories }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- JS for mobile tap overlay -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".game-card").forEach(card => {
        // Toggle overlay on tap for mobile
        card.addEventListener("click", function () {
            const overlay = this.querySelector(".overlay");
            overlay.style.opacity = overlay.style.opacity === "1" ? "0" : "1";
        });

        // Desktop hover
        card.addEventListener("mouseover", function () {
            this.querySelector(".overlay").style.opacity = "1";
        });
        card.addEventListener("mouseout", function () {
            this.querySelector(".overlay").style.opacity = "0";
        });
    });
});
</script>
@endsection
