@extends('layouts.app')

@section('content')
<div class="relative w-full h-screen flex flex-col" id="gameWrapper">

    <!-- Top Control Bar -->
    <div id="fullscreenBar" class="flex justify-between items-center px-4 py-3 z-50 ">
        <!-- Fullscreen toggle button -->
        <button id="fullscreenToggle"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-md transition duration-300">
                ⛶
        </button>

        <!-- Exit game button -->
        <a href="{{ route('casino.bonus.index') }}" 
           class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-md transition duration-300">
           ✖
        </a>
    </div>

    <!-- Iframe Container -->
    <div class="flex-1 relative">
        @if(!empty($src))
            <iframe 
                src="{{ $src }}" 
                class="absolute top-0 left-0 w-full border-0 shadow-xl"
                allowfullscreen
                frameborder="0"
                id="casinoGameIframe">
            </iframe>
        @else
            <div class="flex items-center justify-center h-full">
                <p class="text-red-500 text-xl font-semibold">Game URL not provided.</p>
            </div>
        @endif
    </div>
</div>

<script>
    const iframe = document.getElementById('casinoGameIframe');
    const wrapper = document.getElementById('gameWrapper');
    const fullscreenBtn = document.getElementById('fullscreenToggle');
    const fullscreenBar = document.getElementById('fullscreenBar');

    function resizeIframe() {
        const barHeight = fullscreenBar.offsetHeight;
        const totalHeight = wrapper.clientHeight || window.innerHeight;
        if (iframe) iframe.style.height = (totalHeight - barHeight) + 'px';
        if (iframe) iframe.style.width = wrapper.clientWidth + 'px';
    }

    fullscreenBtn.addEventListener('click', () => {
        if (wrapper.classList.contains("is-fullscreen")) {
            // Exit fullscreen
            wrapper.classList.remove("is-fullscreen");
            fullscreenBtn.textContent = "⛶"; 
            wrapper.style.position = "relative";
            wrapper.style.width = "100%";
            wrapper.style.height = "100%"; 
            wrapper.style.background = "transparent";
        } else {
            // Enter fullscreen
            wrapper.classList.add("is-fullscreen");
            fullscreenBtn.textContent = "⛶"; 
            wrapper.style.position = "fixed";
            wrapper.style.inset = "0";
            wrapper.style.width = "100vw";
            wrapper.style.height = "100vh";
            wrapper.style.background = "#000";
        }
        resizeIframe();
    });

    window.addEventListener('load', () => {
        wrapper.classList.add("is-fullscreen");
        fullscreenBtn.textContent = "⛶";
        wrapper.style.background = "#000";
        resizeIframe();

        if (screen.orientation && screen.orientation.lock) {
            screen.orientation.lock("landscape").catch(()=>console.log("Cannot lock orientation"));
        }
    });

    window.addEventListener('resize', resizeIframe);
</script>

<style>
    #gameWrapper.is-fullscreen {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: #000;
    }

    #casinoGameIframe {
        top: 0;
        left: 0;
        position: absolute;
    }
</style>
@endsection
