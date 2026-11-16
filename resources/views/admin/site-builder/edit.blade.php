@extends('layouts.admin')
@section('title', 'Dynamic site builder')
@section('page_title', 'Site builder')
@section('content')
@php
    $definitions = [
        'home' => [
            'badge' => ['label'=>'Hero badge','type'=>'text','help'=>'Small label above the main heading'],
            'title_start' => ['label'=>'Headline start','type'=>'text'],
            'title_highlight' => ['label'=>'Highlighted headline','type'=>'text'],
            'title_end' => ['label'=>'Headline ending','type'=>'text'],
            'description' => ['label'=>'Hero description','type'=>'textarea'],
            'primary_cta' => ['label'=>'Primary button label','type'=>'text'],
            'secondary_cta' => ['label'=>'Secondary button label','type'=>'text'],
            'featured_heading' => ['label'=>'Featured section heading','type'=>'text'],
            'new_heading' => ['label'=>'New games heading','type'=>'text'],
            'explore_heading' => ['label'=>'Explore more heading','type'=>'text'],
            'show_categories' => ['label'=>'Show homepage categories','type'=>'checkbox'],
            'show_featured' => ['label'=>'Show featured games','type'=>'checkbox'],
            'show_new' => ['label'=>'Show new games','type'=>'checkbox'],
            'show_explore' => ['label'=>'Show explore-more section','type'=>'checkbox'],
        ],
        'navigation' => [
            'home_label' => ['label'=>'Home menu text','type'=>'text'],
            'games_label' => ['label'=>'Games menu text','type'=>'text'],
            'categories_label' => ['label'=>'Categories menu text','type'=>'text'],
            'lottery_label' => ['label'=>'Lottery menu text','type'=>'text'],
            'contact_label' => ['label'=>'Contact menu text','type'=>'text'],
            'show_home' => ['label'=>'Show Home link','type'=>'checkbox'],
            'show_games' => ['label'=>'Show Games link','type'=>'checkbox'],
            'show_categories' => ['label'=>'Show Categories menu and category strip','type'=>'checkbox'],
            'show_lottery' => ['label'=>'Show Lottery link','type'=>'checkbox'],
            'show_contact' => ['label'=>'Show Contact link','type'=>'checkbox'],
            'category_labels' => ['label'=>'Category names','type'=>'textarea','help'=>'Comma-separated game categories. Only matching, safe catalog categories appear in the menu.'],
        ],
        'footer' => [
            'text' => ['label'=>'Footer text','type'=>'textarea'],
            'contact_label' => ['label'=>'Contact link label','type'=>'text'],
            'show_contact' => ['label'=>'Show Contact link','type'=>'checkbox'],
            'show_games' => ['label'=>'Show Games link','type'=>'checkbox'],
        ],
        'catalog' => [
            'featured_limit' => ['label'=>'Featured games shown on homepage','type'=>'number','min'=>1,'max'=>18],
            'new_limit' => ['label'=>'New games shown on homepage','type'=>'number','min'=>1,'max'=>18],
            'page_size' => ['label'=>'Games per catalog page','type'=>'number','min'=>12,'max'=>96],
            'suggestion_limit' => ['label'=>'Live search suggestion count','type'=>'number','min'=>1,'max'=>12],
            'category_bar_count' => ['label'=>'Popular categories shown in header','type'=>'number','min'=>1,'max'=>10],
            'provider_enabled' => ['label'=>'Use approved external embeddable game catalog','type'=>'checkbox','help'=>'When disabled, externally supplied playable games are hidden. Locally managed games remain in the database.'],
        ],
        'pwa' => [
            'name' => ['label'=>'Installed app name','type'=>'text'],
            'short_name' => ['label'=>'Short app name','type'=>'text'],
            'description' => ['label'=>'Installed app description','type'=>'textarea'],
            'theme_color' => ['label'=>'Browser theme color','type'=>'color'],
            'background_color' => ['label'=>'Splash-screen background color','type'=>'color'],
            'display' => ['label'=>'App display mode','type'=>'select','options'=>['standalone'=>'Standalone','minimal-ui'=>'Minimal UI','browser'=>'Browser tab']],
        ],
    ];
    $sectionTitle = [
        'home' => 'Homepage Builder',
        'navigation' => 'Header & Menu Manager',
        'footer' => 'Footer Manager',
        'catalog' => 'Game Catalog Controls',
        'pwa' => 'PWA App Configuration',
    ];
@endphp
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">DATABASE-DRIVEN SITE SETTINGS</span>
        <h1 style="margin-top:9px">Dynamic site builder</h1>
        <p>Update public website content without editing code. All changes are validated, saved by section and apply to new requests.</p>
    </div>
    <a class="cms-button" href="{{ route('games.viewIndex') }}" target="_blank" rel="noopener noreferrer">Preview website</a>
</div>
<nav class="cms-settings-tabs" aria-label="CMS editor sections">
    @foreach($sections as $section)
        <a class="cms-button {{ $selected === $section ? 'is-primary' : '' }}"
           href="{{ route('admin.site-builder.edit',['section'=>$section]) }}"
           @if($selected === $section) aria-current="page" @endif>
            {{ $sectionTitle[$section] }}
        </a>
    @endforeach
</nav>
<div class="cms-card">
    <div class="cms-card-head">
        <div><h2>{{ $sectionTitle[$selected] }}</h2>
            <p>Fields are pre-filled from the current saved configuration. Unsaved edits do not affect the website.</p></div>
    </div>
    <form action="{{ route('admin.site-builder.update',['section'=>$selected]) }}" method="POST" class="cms-form-grid">
        @csrf
        @method('PUT')
        @foreach($definitions[$selected] as $name=>$field)
            <div class="cms-field {{ $field['type'] === 'textarea' ? 'is-wide' : '' }}">
                <label for="builder-{{ $name }}">{{ $field['label'] }}</label>
                @if($field['type'] === 'checkbox')
                    <input type="hidden" name="{{ $name }}" value="0">
                    <label style="display:flex;gap:10px;align-items:center">
                        <input id="builder-{{ $name }}" type="checkbox" name="{{ $name }}" value="1" style="width:20px;min-height:20px"
                            @checked((bool) old($name,$config[$name] ?? false))>
                        Enabled
                    </label>
                @elseif($field['type'] === 'textarea')
                    <textarea id="builder-{{ $name }}" name="{{ $name }}" rows="3" maxlength="{{ $name === 'category_labels' ? 350 : 300 }}" required>{{ old($name,$config[$name] ?? '') }}</textarea>
                @elseif($field['type'] === 'select')
                    <select id="builder-{{ $name }}" name="{{ $name }}" required>
                        @foreach($field['options'] as $key=>$label)
                            <option value="{{ $key }}" @selected(old($name, $config[$name] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <input id="builder-{{ $name }}" type="{{ $field['type'] }}" name="{{ $name }}"
                        value="{{ old($name,$config[$name] ?? '') }}"
                        @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                        @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                        @if($field['type'] !== 'number' && $field['type'] !== 'color') maxlength="300" @endif required>
                @endif
                @if(!empty($field['help']))<small>{{ $field['help'] }}</small>@endif
                @error($name)<small role="alert" style="color:#b12626">{{ $message }}</small>@enderror
            </div>
        @endforeach
        <div class="cms-field is-wide">
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit">Save {{ $sectionTitle[$selected] }}</button>
                <a class="cms-button" href="{{ route('admin.site-builder.edit',['section'=>$selected]) }}">Discard unsaved edits</a>
            </div>
        </div>
    </form>
    @if($selected === 'pwa')
        <div class="cms-secret-hint">The app name, colors and display mode update the live web manifest. The static gamepad PNG icons remain unchanged; regenerating custom install icons requires a separate icon build.</div>
    @elseif($selected === 'catalog')
        <div class="cms-secret-hint">The external feed remains restricted to safe approved embeds, and prohibited gambling categories stay blocked. These settings do not erase game or user records.</div>
    @endif
</div>
@endsection
