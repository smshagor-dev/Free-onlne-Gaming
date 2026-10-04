@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Admin Profile</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <!-- Profile Header -->
                <div class="px-4 py-5 sm:px-6 bg-gradient-to-r from-blue-500 to-indigo-600">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($admin->photo)
                                <img class="h-16 w-16 rounded-full object-cover border-2 border-white" 
                                     src="{{ asset('storage/' . $admin->photo) }}" 
                                     alt="{{ $admin->name }}'s profile photo">
                            @else
                                <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center text-blue-600 text-2xl font-bold">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg leading-6 font-medium text-white">{{ $admin->name }}</h3>
                            <p class="mt-1 text-sm text-blue-100">{{ $admin->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Full name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $admin->name }}</dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $admin->email }}</dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Email address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $admin->mobile_number }}</dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Account created</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $admin->created_at->format('M d, Y') }} ({{ $admin->created_at->diffForHumans() }})
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Last updated</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $admin->updated_at->format('M d, Y') }} ({{ $admin->updated_at->diffForHumans() }})
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Profile Actions -->
                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.editProfile') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-edit mr-2"></i> Edit Profile
                    </a>
                    <a href="{{ route('admin.editPassword') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection