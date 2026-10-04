@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Settings</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.setting.update', $setting->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px" id="myTab" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="general-tab" data-tabs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="false">General</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600" id="seo-tab" data-tabs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600" id="analytics-tab" data-tabs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="false">Analytics</button>
                </li>
                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600" id="mail-tab" data-tabs-target="#mail" type="button" role="tab" aria-controls="mail" aria-selected="false">Mail Settings</button>
                </li>
            </ul>
        </div>

        <div id="myTabContent">
            <!-- General Tab -->
            <div class="hidden p-4 rounded-lg bg-gray-50" id="general" role="tabpanel" aria-labelledby="general-tab">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Site Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $setting->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="title" class="block mb-2 text-sm font-medium text-gray-900">Site Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $setting->title) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Site Description</label>
                        <textarea id="description" name="description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('description', $setting->description) }}</textarea>
                    </div>
                    
                    <div>
                        <label for="logo" class="block mb-2 text-sm font-medium text-gray-900">Logo</label>
                        <input type="file" id="logo" name="logo" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100">
                        @if($setting->logo)
                            <div class="mt-2">
                                <img src="{{ Storage::url($setting->logo) }}" alt="Current Logo" class="h-20">
                                <p class="text-sm text-gray-500 mt-1">Current logo</p>
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <label for="favicon" class="block mb-2 text-sm font-medium text-gray-900">Favicon</label>
                        <input type="file" id="favicon" name="favicon" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100">
                        @if($setting->favicon)
                            <div class="mt-2">
                                <img src="{{ Storage::url($setting->favicon) }}" alt="Current Favicon" class="h-10">
                                <p class="text-sm text-gray-500 mt-1">Current favicon</p>
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Phone</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $setting->phone) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $setting->email) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label for="site_currency" class="block mb-2 text-sm font-medium text-gray-900">Site Currency</label>
                        <select id="site_currency" name="site_currency" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @foreach($countries as $country)
                                <option value="{{ $country->currency }}" {{ old('site_currency', $setting->site_currency) == $country->currency ? 'selected' : '' }}>
                                    {{ $country->currency }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="currency_symble" class="block mb-2 text-sm font-medium text-gray-900">Currency Symbol</label>
                        <select id="currency_symble" name="currency_symble" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @foreach($countries as $country)
                                <option value="{{ $country->currency_symbol }}" {{ old('currency_symble', $setting->currency_symble) == $country->currency_symbol ? 'selected' : '' }}>
                                    {{ $country->currency_symbol }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- SEO Tab -->
            <div class="hidden p-4 rounded-lg bg-gray-50" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="seo_title" class="block mb-2 text-sm font-medium text-gray-900">SEO Title</label>
                        <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $setting->seo_title) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="meta_tag" class="block mb-2 text-sm font-medium text-gray-900">Meta Tags</label>
                        <input type="text" id="meta_tag" name="meta_tag" value="{{ old('meta_tag', $setting->meta_tag) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="seo_description" class="block mb-2 text-sm font-medium text-gray-900">SEO Description</label>
                        <textarea id="seo_description" name="seo_description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('seo_description', $setting->seo_description) }}</textarea>
                    </div>
                    
                    <div>
                        <label for="thumbnail_image" class="block mb-2 text-sm font-medium text-gray-900">Thumbnail Image</label>
                        <input type="file" id="thumbnail_image" name="thumbnail_image" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100">
                        @if($setting->thumbnail_image)
                            <div class="mt-2">
                                <img src="{{ Storage::url($setting->thumbnail_image) }}" alt="Current Thumbnail" class="h-20">
                                <p class="text-sm text-gray-500 mt-1">Current thumbnail</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Analytics Tab -->
            <div class="hidden p-4 rounded-lg bg-gray-50" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="adsense_code" class="block mb-2 text-sm font-medium text-gray-900">Adsense Code</label>
                        <textarea id="adsense_code" name="adsense_code" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('adsense_code', $setting->adsense_code) }}</textarea>
                    </div>
                    
                    <div>
                        <label for="google_analytics_code" class="block mb-2 text-sm font-medium text-gray-900">Google Analytics Code</label>
                        <textarea id="google_analytics_code" name="google_analytics_code" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('google_analytics_code', $setting->google_analytics_code) }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Mail Settings Tab -->
            <div class="hidden p-4 rounded-lg bg-gray-50" id="mail" role="tabpanel" aria-labelledby="mail-tab">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="MAIL_MAILER" class="block mb-2 text-sm font-medium text-gray-900">Mail Driver</label>
                        <input type="text" id="MAIL_MAILER" name="MAIL_MAILER" value="{{ old('MAIL_MAILER', $setting->MAIL_MAILER) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_HOST" class="block mb-2 text-sm font-medium text-gray-900">Mail Host</label>
                        <input type="text" id="MAIL_HOST" name="MAIL_HOST" value="{{ old('MAIL_HOST', $setting->MAIL_HOST) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_PORT" class="block mb-2 text-sm font-medium text-gray-900">Mail Port</label>
                        <input type="text" id="MAIL_PORT" name="MAIL_PORT" value="{{ old('MAIL_PORT', $setting->MAIL_PORT) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_USERNAME" class="block mb-2 text-sm font-medium text-gray-900">Mail Username</label>
                        <input type="text" id="MAIL_USERNAME" name="MAIL_USERNAME" value="{{ old('MAIL_USERNAME', $setting->MAIL_USERNAME) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_PASSWORD" class="block mb-2 text-sm font-medium text-gray-900">Mail Password</label>
                        <input type="text" id="MAIL_PASSWORD" name="MAIL_PASSWORD" value="{{ old('MAIL_PASSWORD', $setting->MAIL_PASSWORD) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_ENCRYPTION" class="block mb-2 text-sm font-medium text-gray-900">Mail Encryption</label>
                        <input type="text" id="MAIL_ENCRYPTION" name="MAIL_ENCRYPTION" value="{{ old('MAIL_ENCRYPTION', $setting->MAIL_ENCRYPTION) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_FROM_ADDRESS" class="block mb-2 text-sm font-medium text-gray-900">From Address</label>
                        <input type="email" id="MAIL_FROM_ADDRESS" name="MAIL_FROM_ADDRESS" value="{{ old('MAIL_FROM_ADDRESS', $setting->MAIL_FROM_ADDRESS) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    
                    <div>
                        <label for="MAIL_FROM_NAME" class="block mb-2 text-sm font-medium text-gray-900">From Name</label>
                        <input type="text" id="MAIL_FROM_NAME" name="MAIL_FROM_NAME" value="{{ old('MAIL_FROM_NAME', $setting->MAIL_FROM_NAME) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Update Settings</button>
        </div>
    </form>
</div>

<script>
    // Simple tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('[data-tabs-target]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');
        
        // Show first tab by default
        tabContents[0].classList.remove('hidden');
        document.querySelector('#general-tab').classList.add('border-blue-500', 'text-blue-600');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = document.querySelector(this.getAttribute('data-tabs-target'));
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Remove active styles from all tabs
                tabs.forEach(t => {
                    t.classList.remove('border-blue-500', 'text-blue-600');
                    t.classList.add('border-transparent', 'hover:text-gray-600');
                });
                
                // Show the selected tab content
                target.classList.remove('hidden');
                
                // Add active styles to the selected tab
                this.classList.remove('border-transparent', 'hover:text-gray-600');
                this.classList.add('border-blue-500', 'text-blue-600');
            });
        });
    });
</script>
@endsection