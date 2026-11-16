@extends('layouts.admin')
@section('title','Site settings')
@section('page_title','Site configuration')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">CENTRAL CONFIGURATION</span>
        <h1 style="margin-top:10px">Site settings</h1>
        <p>Manage your website identity, visual assets, SEO, analytics, and email delivery in one place. Existing values stay in place until replaced.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="cms-button">Back to dashboard</a>
</div>

<div class="cms-card">
    <div class="cms-settings-tabs" role="tablist" aria-label="Site settings sections">
        @foreach([
            'general'=>'General',
            'brand'=>'Brand assets',
            'seo'=>'SEO & metadata',
            'analytics'=>'Analytics',
            'mail'=>'SMTP & email',
        ] as $key=>$label)
            <button id="cms-tab-{{ $key }}" type="button" data-cms-tab="{{ $key }}"
                role="tab" aria-controls="cms-panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                tabindex="{{ $loop->first ? 0 : -1 }}">{{ $label }}</button>
        @endforeach
    </div>
        <section id="cms-panel-general" data-cms-panel="general" class="cms-settings-panel" role="tabpanel" aria-labelledby="cms-tab-general">
        <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" data-cms-settings-form="general">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="general">
            <h2>Website identity & contact</h2>
            <p>These values identify the website and its contact details.</p>
            <div class="cms-form-grid">
                <div class="cms-field"><label for="site-name">Site name</label>
                    <input id="site-name" name="name" type="text" maxlength="255" value="{{ old('name',$setting->name) }}" placeholder="Free Games">
                    @error('name')<small role="alert">{{ $message }}</small>@enderror
                </div>
                <div class="cms-field"><label for="site-title">Site title</label>
                    <input id="site-title" name="title" type="text" maxlength="255" value="{{ old('title',$setting->title) }}">
                </div>
                <div class="cms-field is-wide"><label for="site-description">Description</label>
                    <textarea id="site-description" name="description" rows="3">{{ old('description',$setting->description) }}</textarea>
                </div>
                <div class="cms-field"><label for="site-email">Public contact email</label>
                    <input id="site-email" name="email" type="email" value="{{ old('email',$setting->email) }}">
                </div>
                <div class="cms-field"><label for="site-phone">Public contact phone</label>
                    <input id="site-phone" name="phone" type="tel" value="{{ old('phone',$setting->phone) }}">
                </div>
                <div class="cms-field"><label for="site-currency">Site currency</label>
                    <select id="site-currency" name="site_currency">
                        @php($currentCurrency = old('site_currency',$setting->site_currency ?: 'USD'))
                        @php($activeCurrency = $currencies->firstWhere('code', $currentCurrency))
                        <option value="{{ $currentCurrency }}" data-symbol="{{ $activeCurrency?->symbol ?? $setting->currency_symble ?? '' }}">{{ $currentCurrency }}{{ $activeCurrency ? ' — '.$activeCurrency->name : '' }}</option>
                        @forelse($currencies as $currency)
                            @if($currency->code !== $currentCurrency)
                                <option value="{{ $currency->code }}" data-symbol="{{ $currency->symbol }}">
                                    {{ $currency->code }} — {{ $currency->name }} ({{ $currency->symbol }})
                                </option>
                            @endif
                        @empty
                            @foreach($countries as $country)
                                @if($country->currency !== $currentCurrency)
                                    <option value="{{ $country->currency }}" data-symbol="{{ $country->currency_symbol }}">{{ $country->currency }}</option>
                                @endif
                            @endforeach
                        @endforelse
                    </select>
                </div>
                <div class="cms-field"><label for="currency-symbol">Currency symbol</label>
                    <input id="currency-symbol" name="currency_symble" type="text" maxlength="10" value="{{ old('currency_symble',$setting->currency_symble) }}">
                    <small>Blank keeps the saved symbol. Selecting a different currency fills its suggested symbol.</small>
                </div>
            </div>
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save General settings</button>
                <small>Only general fields are updated; other settings remain unchanged.</small>
            </div>
        </form>
        </section>

        <section id="cms-panel-brand" data-cms-panel="brand" class="cms-settings-panel" role="tabpanel" aria-labelledby="cms-tab-brand" hidden>
        <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" enctype="multipart/form-data" data-cms-settings-form="brand">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="brand">
            <h2>Branding & media</h2>
            <p>Uploading is optional. Not selecting a new file will keep the saved image.</p>
            <div class="cms-form-grid">
                @foreach([
                    ['logo','Website logo','image/png,image/jpeg,image/gif'],
                    ['favicon','Favicon','image/png,image/x-icon,image/vnd.microsoft.icon'],
                    ['thumbnail_image','Social preview image','image/png,image/jpeg,image/gif'],
                ] as [$key,$label,$accept])
                    <div class="cms-field">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <input id="{{ $key }}" name="{{ $key }}" type="file" accept="{{ $accept }}">
                        @if($setting->{$key})
                            <div style="padding:9px;background:#f4f8fd;border:1px solid #dfeaf5;border-radius:10px">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->{$key}) }}" alt="Saved {{ $label }}"
                                    style="max-height:65px;max-width:180px;object-fit:contain">
                                <small>Existing file kept unless replaced</small>
                            </div>
                        @else
                            <small>No custom image uploaded.</small>
                        @endif
                        @error($key)<small role="alert">{{ $message }}</small>@enderror
                    </div>
                @endforeach
            </div>
            <div class="cms-secret-hint">Installed PWA app icons are distributed with the frontend bundle. Custom site images do not automatically regenerate installed PWA icons.</div>
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Brand settings</button>
                <small>Only brand fields are updated; other settings remain unchanged.</small>
            </div>
        </form>
        </section>

        <section id="cms-panel-seo" data-cms-panel="seo" class="cms-settings-panel" role="tabpanel" aria-labelledby="cms-tab-seo" hidden>
        <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" data-cms-settings-form="seo">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="seo">
            @php($suggestedSeoTitle = $setting->title ?: ($setting->name ?: 'Free Games'))
            @php($suggestedSeoDescription = $setting->description ?: 'Discover and play free browser games online.')
            <h2>Search metadata</h2>
            <p>SEO values are saved separately from other tabs. When not configured, the fields suggest your existing site name and description; click Save SEO metadata settings to store them.</p>
            <div class="cms-form-grid">
                <div class="cms-field"><label for="seo_title">SEO title</label>
                    <input id="seo_title" name="seo_title" type="text" maxlength="255" value="{{ old('seo_title', filled($setting->seo_title) ? $setting->seo_title : $suggestedSeoTitle) }}" required>
                </div>
                <div class="cms-field"><label for="meta_tag">Meta tags</label>
                    <input id="meta_tag" name="meta_tag" type="text" maxlength="255" value="{{ old('meta_tag',$setting->meta_tag) }}" placeholder="free browser games, HTML5 games">
                    <small>Optional keywords. Search engines typically prioritize the title and description.</small>
                </div>
                <div class="cms-field is-wide"><label for="seo_description">SEO description</label>
                    <textarea id="seo_description" name="seo_description" rows="4" maxlength="500" required>{{ old('seo_description', filled($setting->seo_description) ? $setting->seo_description : $suggestedSeoDescription) }}</textarea>
                </div>
            </div>
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save SEO metadata settings</button>
                <small>Only seo metadata fields are updated; other settings remain unchanged.</small>
            </div>
        </form>
        </section>

        <section id="cms-panel-analytics" data-cms-panel="analytics" class="cms-settings-panel" role="tabpanel" aria-labelledby="cms-tab-analytics" hidden>
        <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" data-cms-settings-form="analytics">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="analytics">
            <h2>Marketing integrations</h2>
            <p>These values remain saved in your settings record; use only trusted vendor snippets.</p>
            <div class="cms-form-grid">
                <div class="cms-field is-wide"><label for="google_analytics_code">Google Analytics configuration</label>
                    <textarea id="google_analytics_code" name="google_analytics_code" rows="5" spellcheck="false">{{ old('google_analytics_code',$setting->google_analytics_code) }}</textarea>
                </div>
                <div class="cms-field is-wide"><label for="adsense_code">Google AdSense configuration</label>
                    <textarea id="adsense_code" name="adsense_code" rows="5" spellcheck="false">{{ old('adsense_code',$setting->adsense_code) }}</textarea>
                </div>
            </div>
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Analytics settings</button>
                <small>Only analytics fields are updated; other settings remain unchanged.</small>
            </div>
        </form>
        </section>

        <section id="cms-panel-mail" data-cms-panel="mail" class="cms-settings-panel" role="tabpanel" aria-labelledby="cms-tab-mail" hidden>
        <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" data-cms-settings-form="mail">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_tab" value="mail">
            <h2>SMTP & email delivery</h2>
            <p>Mail configuration is applied to Laravel on the next request. Never share your SMTP password in public logs or screenshots.</p>
            <div class="cms-form-grid">
                <div class="cms-field"><label for="MAIL_MAILER">Mail driver</label>
                    <select id="MAIL_MAILER" name="MAIL_MAILER">
                        @foreach(['smtp'=>'SMTP','log'=>'Log (development)','sendmail'=>'Sendmail','array'=>'Array (testing)'] as $value=>$label)
                            <option value="{{ $value }}" @selected(old('MAIL_MAILER', $setting->MAIL_MAILER ?: config('mail.default')) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cms-field"><label for="MAIL_HOST">SMTP host</label>
                    <input id="MAIL_HOST" name="MAIL_HOST" type="text" maxlength="255"
                        value="{{ old('MAIL_HOST',$setting->MAIL_HOST) }}" autocomplete="off" placeholder="smtp.yourprovider.com">
                </div>
                <div class="cms-field"><label for="MAIL_PORT">SMTP port</label>
                    <input id="MAIL_PORT" name="MAIL_PORT" type="number" min="1" max="65535" value="{{ old('MAIL_PORT',$setting->MAIL_PORT) }}" placeholder="587">
                </div>
                <div class="cms-field"><label for="MAIL_ENCRYPTION">Connection security</label>
                    <select id="MAIL_ENCRYPTION" name="MAIL_ENCRYPTION">
                        @foreach(['tls'=>'STARTTLS (typically 587)','ssl'=>'SSL/TLS (typically 465)','none'=>'Default SMTP transport'] as $value=>$label)
                            <option value="{{ $value }}" @selected(old('MAIL_ENCRYPTION',$setting->MAIL_ENCRYPTION ?: 'tls') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cms-field"><label for="MAIL_USERNAME">SMTP username</label>
                    <input id="MAIL_USERNAME" name="MAIL_USERNAME" type="text" maxlength="255" value="{{ old('MAIL_USERNAME',$setting->MAIL_USERNAME) }}" autocomplete="off">
                </div>
                <div class="cms-field"><label for="MAIL_PASSWORD">SMTP password</label>
                    <input id="MAIL_PASSWORD" name="MAIL_PASSWORD" type="password" value="" autocomplete="new-password" maxlength="1024"
                        placeholder="{{ filled($setting->getRawOriginal('MAIL_PASSWORD')) ? 'Saved password • leave empty to keep' : 'Enter app password / SMTP secret' }}">
                    <small>{{ filled($setting->getRawOriginal('MAIL_PASSWORD')) ? 'Credential saved. Leaving blank preserves the existing secret.' : 'No SMTP credential saved in CMS.' }}</small>
                </div>
                <div class="cms-field"><label for="MAIL_FROM_ADDRESS">From email address</label>
                    <input id="MAIL_FROM_ADDRESS" name="MAIL_FROM_ADDRESS" type="email" value="{{ old('MAIL_FROM_ADDRESS',$setting->MAIL_FROM_ADDRESS) }}">
                </div>
                <div class="cms-field"><label for="MAIL_FROM_NAME">From display name</label>
                    <input id="MAIL_FROM_NAME" name="MAIL_FROM_NAME" type="text" maxlength="255" value="{{ old('MAIL_FROM_NAME',$setting->MAIL_FROM_NAME) }}">
                </div>
            </div>
            <div class="cms-secret-hint">Only an explicitly entered new password will replace the saved one. SMTP test emails go only to the signed-in administrator's address.</div>
            <div class="cms-settings-actions">
                <button class="cms-button is-primary" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save SMTP & email settings</button>
                <small>Only smtp & email fields are updated; other settings remain unchanged.</small>
            </div>
        </form>
        </section>


</div>
<div class="cms-card" style="max-width:700px">
    <div class="cms-card-head"><div><h2>Send SMTP test</h2><p>Verify the active SMTP configuration without exposing the secret.</p></div></div>
    <form method="POST" action="{{ route('admin.setting.mail.test') }}">
        @csrf
        <button class="cms-button" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send test email to my admin account</button>
    </form>
</div>
@endsection
