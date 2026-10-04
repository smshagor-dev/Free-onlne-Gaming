@extends('layouts.app')

@section('content')



<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Edit Profile Card -->
        <div class="bg-transparent rounded-lg shadow-md overflow-hidden border border-blue-400 mb-8">
            <div class="bg-[#0b141d] px-6 py-4 text-white">
                <h2 class="text-2xl font-bold">Edit Profile</h2>
            </div>
            <div class="p-6 bg-slate-800 bg-opacity-50">
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-blue-200 font-medium mb-2">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-blue-200 font-medium mb-2">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('email')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mobile -->
                        <div>
                            <label for="mobile" class="block text-blue-200 font-medium mb-2">Mobile Number</label>
                            <input type="text" name="mobile" id="mobile" value="{{ old('mobile', $user->mobile_number) }}"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('mobile')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label for="date_of_birth" class="block text-blue-200 font-medium mb-2">Date of Birth
                                @if($user->date_of_birth)
                                ({{ \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') }})
                                @else
                                (Not Provided)
                                @endif
                            </label>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                value="{{ old('date_of_birth', $user->date_of_birth) }}"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('date_of_birth')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Profile Photo -->
                        <div class="md:col-span-2">
                            <label for="photo" class="block text-blue-200 font-medium mb-2">Profile Photo</label>
                            <div class="flex items-center space-x-4">
                                @if($user->photo)
                                <img src="{{ asset('storage/'.$user->photo) }}" alt="Current Profile Photo" class="w-16 h-16 rounded-full object-cover border-2 border-blue-300">
                                @else
                                <div class="w-16 h-16 rounded-full bg-blue-700 bg-opacity-50 flex items-center justify-center border border-blue-400">
                                    <span class="text-blue-200 text-xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                @endif
                                <input type="file" name="photo" id="photo"
                                    class="flex-1 px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @error('photo')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('user.profile') }}" class="px-4 py-2 bg-transparent border border-blue-400 text-blue-200 rounded-lg hover:bg-slate-800 hover:bg-opacity-30 transition duration-200">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition duration-200 border border-blue-400">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="bg-transparent rounded-lg shadow-md overflow-hidden border border-blue-400">
            <div class="bg-slate-800 px-6 py-4 text-white">
                <h2 class="text-2xl font-bold">Change Password</h2>
            </div>
            <div class="p-6 bg-[#0b141d] bg-opacity-50">
                <form action="{{ route('user.profile.changePassword') }}" method="POST">
                    @csrf
                    <div class="space-y-4 mb-6">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-blue-200 font-medium mb-2">Current Password</label>
                            <input type="password" name="current_password" id="current_password"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('current_password')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-blue-200 font-medium mb-2">New Password</label>
                            <input type="password" name="new_password" id="new_password"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('new_password')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="new_password_confirmation" class="block text-blue-200 font-medium mb-2">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                class="w-full px-4 py-2 bg-slate-800 bg-opacity-50 border border-blue-400 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition duration-200 border border-blue-400">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection