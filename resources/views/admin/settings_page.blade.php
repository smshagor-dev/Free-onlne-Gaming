@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Settings
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage your application settings and configurations
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button type="button" id="refreshBtn" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- App Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-cog text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">App Settings</h3>
                            <p class="mt-1 text-sm text-gray-500">Configure application-wide settings and preferences</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.setting.edit') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Manage settings
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-id-card-alt text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">KYC Settings</h3>
                            <p class="mt-1 text-sm text-gray-500">Manage identity verification requirements and processes</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.kyc.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Configure KYC
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-image text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Banner Management</h3>
                            <p class="mt-1 text-sm text-gray-500">Control promotional banners and featured content</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.banners.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Manage banners
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Point Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-coins text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Points System</h3>
                            <p class="mt-1 text-sm text-gray-500">Configure rewards, points, and loyalty programs</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.points.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Points settings
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Level Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-layer-group text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Level System</h3>
                            <p class="mt-1 text-sm text-gray-500">Set up user levels and progression requirements</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.levels.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Level configuration
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-user-friends text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Referral Program</h3>
                            <p class="mt-1 text-sm text-gray-500">Manage referral rewards and program settings</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.referral.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Referral settings
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-gray-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-file-alt text-gray-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Page Management</h3>
                            <p class="mt-1 text-sm text-gray-500">Edit static pages like Terms, Privacy Policy, etc.</p>
                            <div class="mt-4">
                                <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Manage pages
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-pink-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-bell text-pink-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                            <p class="mt-1 text-sm text-gray-500">Configure email and push notification templates</p>
                            <div class="mt-4">
                                <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Notification settings
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-shield-alt text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Security</h3>
                            <p class="mt-1 text-sm text-gray-500">Security settings, 2FA, and access controls</p>
                            <div class="mt-4">
                                <a href="#" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Security configuration
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Country and Currency Setting -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-3 icon-wrapper">
                            <i class="fas fa-shield-alt text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Country & Currency</h3>
                            <p class="mt-1 text-sm text-gray-500">Manage Site Country and Currency</p>
                            <div class="mt-4">
                                <a href="{{route('admin.countries.index')}}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500">
                                    Manage configuration
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    .card-hover {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: #e5e7eb;
    }
    
    .icon-wrapper {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover .icon-wrapper {
        background-color: #2563eb !important;
        color: white !important;
    }
    
    .card-hover:hover .icon-wrapper i {
        color: white !important;
    }
</style>

<script>
    document.getElementById('refreshBtn').addEventListener('click', function() {
        location.reload();
    });
</script>


@endsection