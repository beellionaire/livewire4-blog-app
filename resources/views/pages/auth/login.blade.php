<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark"> 
<head>
    <!-- Memanggil tag <head> bawaan aplikasi Anda (termasuk Tailwind/Vite) -->
    @include('partials.head')
    <title>{{ __('Log in') }} - {{ config('app.name', 'YourBrand') }}</title>
</head>
<body class="min-h-screen bg-white antialiased dark:bg-neutral-950">
    
    <!-- Wrapper Utama Layar Penuh (min-h-screen dan w-full) -->
    <div class="flex min-h-screen w-full">
        
        <!-- SECTION KIRI: Gambar Cover 
             Responsif: disembunyikan di HP (hidden), tampil 50% di desktop (lg:flex lg:w-1/2) 
        -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-zinc-900 items-center justify-center overflow-hidden">
            <!-- Gambar Background -->
            <img 
                src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop" 
                alt="Premium Background" 
                class="absolute inset-0 object-cover w-full h-full opacity-40"
            />
            
            <!-- Overlay Gradient agar teks di atasnya mudah dibaca -->
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/40 to-transparent"></div>

            <!-- Konten Teks Bagian Kiri -->
            <div class="relative z-10 flex flex-col justify-between w-full h-full p-12 lg:p-20 text-white">
                <div>
                    <!-- Logo / Brand -->
                    <span class="text-2xl font-bold tracking-wider uppercase flex items-center gap-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        YourBrand.
                    </span>
                </div>
                <div>
                    <h2 class="text-3xl lg:text-4xl font-semibold leading-tight text-white mb-6">
                        "Membangun masa depan digital dengan teknologi yang elegan dan sistem terintegrasi."
                    </h2>
                    <p class="text-zinc-400 font-medium tracking-wide text-lg">— Nabil, Full-stack Developer</p>
                </div>
            </div>
        </div>

        <!-- SECTION KANAN: Form Login 
             Responsif: Lebar 100% di HP (w-full), Lebar 50% di desktop (lg:w-1/2) 
        -->
        <div class="flex flex-col justify-center w-full lg:w-1/2 px-6 py-12 md:px-16 lg:px-24 relative">
            
            <!-- Tombol Kembali ke Home di pojok kanan atas -->
            <a href="{{ route('home') }}" class="absolute top-8 right-8 text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Home
            </a>

            <!-- Container Khusus Form agar tidak terlalu melebar (max-w-md) -->
            <div class="w-full max-w-md mx-auto">
                
                <!-- Header Form -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ __('Log in to your account') }}</h1>
                    <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                        {{ __('Enter your email and password below to log in') }}
                    </p>
                </div>

                <!-- Session Status (Pesan Error/Sukses) -->
                <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

                <x-passkey-verify />

                <!-- Form Area -->
                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
                    @csrf

                    <!-- Email Address Input -->
                    <flux:input
                        name="email"
                        :label="__('Email address')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />

                    <!-- Password Input -->
                    <div class="relative">
                        <flux:input
                            name="password"
                            :label="__('Password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            :placeholder="__('Password')"
                            viewable
                        />

                        <!-- Forgot Password Link yang diposisikan absolut di sudut kanan atas input password -->
                        @if (Route::has('password.request'))
                            <flux:link class="absolute top-0 text-sm end-0 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between">
                        <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />
                    </div>

                    <!-- Submit Button -->
                    <flux:button variant="primary" type="submit" class="w-full mt-2 py-3 text-base" data-test="login-button">
                        {{ __('Log in') }}
                    </flux:button>
                </form>

                <!-- Sign Up Link Area -->
                <div class="text-center text-zinc-600 dark:text-zinc-400 mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <flux:link :href="route('register')" wire:navigate class="font-semibold text-zinc-900 dark:text-white hover:underline ml-1">
                        {{ __('Sign up') }}
                    </flux:link>
                </div>
            </div>
        </div>

    </div>
</body>
</html>