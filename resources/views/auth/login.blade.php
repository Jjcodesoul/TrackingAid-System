<x-guest-layout>
    <div class="fixed inset-0 flex flex-col md:flex-row overflow-hidden bg-white">
        
        {{-- Left Panel: System Identity (Visible on MD and up) --}}
        <div class="hidden md:flex md:w-1/2 bg-[#2ecc71] items-center justify-center p-12 text-white relative">
            <div class="relative z-10 max-w-md">
                <h1 class="text-5xl font-extrabold tracking-tighter mb-4">TrackingAid</h1>
                <p class="text-emerald-100 text-lg font-medium opacity-90">
                    Barangay-level post-disaster response and inventory management system. 
                </p>
                
                <div class="mt-10 space-y-6">
                    <div class="flex items-center space-x-4 bg-white/10 p-4 rounded-xl backdrop-blur-sm">
                        <span class="text-2xl">🛡️</span>
                        <span class="font-bold">Secure Access Control</span>
                    </div>
                    <div class="flex items-center space-x-4 bg-white/10 p-4 rounded-xl backdrop-blur-sm">
                        <span class="text-2xl">📊</span>
                        <span class="font-bold">Real-time Resource Mapping</span>
                    </div>
                </div>
            </div>
            
            {{-- Subtle Map Decor Background --}}
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg class="w-full h-full" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 0 L100 0 L100 100 L0 100 Z" fill="url(#grid)" />
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                </svg>
            </div>
        </div>

        {{-- Right Panel: Login Form --}}
        <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-gray-50">
            <div class="w-full max-w-md">
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-gray-800">System Login</h2>
                    <p class="text-gray-500 font-medium">Authorized personnel only.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-xs font-black uppercase text-gray-400" />
                        <x-text-input id="email" class="block mt-1 w-full border-gray-200 focus:border-[#2ecc71] focus:ring-[#2ecc71] rounded-xl shadow-sm" 
                                    type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <x-input-label for="password" :value="__('Password')" class="text-xs font-black uppercase text-gray-400" />
                            @if (Route::has('password.request'))
                                <a class="text-xs font-bold text-[#2ecc71] hover:underline" href="{{ route('password.request') }}">
                                    {{ __('Forgot?') }}
                                </a>
                            @endif
                        </div>
                        <x-text-input id="password" class="block mt-1 w-full border-gray-200 focus:border-[#2ecc71] focus:ring-[#2ecc71] rounded-xl shadow-sm"
                                    type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#2ecc71] focus:ring-[#2ecc71] shadow-sm" name="remember">
                            <span class="ms-2 text-sm text-gray-600 font-bold">{{ __('Remember station') }}</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#2ecc71] hover:bg-[#27ae60] text-white py-4 rounded-xl font-black text-lg shadow-lg shadow-emerald-100 transition-all active:scale-95">
                            {{ __('Login') }}
                        </button>
                    </div>
                </form>

                <p class="mt-8 text-center text-xs text-gray-400 font-medium">
                    &copy; 2026 TrackingAid. All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>