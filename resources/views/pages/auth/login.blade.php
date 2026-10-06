<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark"> 
<head>
    <!-- Memanggil tag <head> bawaan aplikasi Anda (termasuk Tailwind/Vite) -->
    @include('partials.head')
    <title>{{ __('Log in') }} - {{ config('app.name', 'YourBrand') }}</title>
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased selection:bg-indigo-500 selection:text-white">
    
    <!-- Wrapper Utama Layar Penuh (min-h-screen dan w-full) -->
    <div class="flex min-h-screen w-full relative overflow-hidden">
        
        <!-- SECTION KIRI: Gambar Cover & Ambient Glass Layer
             Responsif: disembunyikan di HP (hidden), tampil 50% di desktop (lg:flex lg:w-1/2) 
        -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-br from-indigo-950 via-zinc-950 to-purple-950 items-center justify-center overflow-hidden border-r border-white/10">
            <!-- Gambar Background dengan Blending -->
            <img 
                src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop" 
                alt="Premium Background" 
                class="absolute inset-0 object-cover w-full h-full opacity-30 mix-blend-overlay scale-105 transform duration-1000"
            />
            
            <!-- Ambient Decorative Gradient Blob -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-[120px] pointer-events-none"></div>

            <!-- Overlay Gradient agar teks di atasnya mudah dibaca -->
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950/90 via-zinc-950/40 to-transparent"></div>

            <!-- Konten Teks Bagian Kiri -->
            <div class="relative z-10 flex flex-col justify-between w-full h-full p-12 lg:p-20 text-white">
                <div>
                    <!-- Logo / Brand -->
                    <span class="text-xl font-bold tracking-wider uppercase flex items-center gap-2.5 bg-white/10 backdrop-blur-md px-4 py-2 rounded-2xl w-fit border border-white/15 shadow-lg">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        YourBrand.
                    </span>
                </div>
                <div>
                    <h2 class="text-3xl lg:text-4xl font-semibold leading-snug text-white/95 mb-6 tracking-tight">
                        "Membangun masa depan digital dengan teknologi yang elegan dan sistem terintegrasi."
                    </h2>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-500/30 border border-indigo-400/30 flex items-center justify-center text-xs font-bold text-indigo-300">N</div>
                        <p class="text-zinc-400 font-medium tracking-wide text-sm">— Nabil, Full-stack Developer</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION KANAN: Form Login Glassmorphism Panel
             Responsif: Lebar 100% di HP (w-full), Lebar 50% di desktop (lg:w-1/2) 
        -->
        <div class="flex flex-col justify-center w-full lg:w-1/2 px-6 py-12 md:px-16 lg:px-24 relative bg-zinc-50 dark:bg-zinc-950">
            
            <!-- Tombol Kembali ke Home di pojok kanan atas -->
            <a href="{{ route('home') }}" class="absolute top-8 right-8 text-xs font-semibold tracking-wider uppercase text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-all duration-200 flex items-center gap-2 px-4 py-2 rounded-xl bg-zinc-100/80 dark:bg-zinc-900/80 backdrop-blur-md border border-zinc-200 dark:border-zinc-800 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Home
            </a>

            <!-- Container Khusus Form dengan Glassmorphism Card Wrapper -->
            <div class="w-full max-w-md mx-auto bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl p-8 sm:p-10 rounded-3xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none transition-all">
                
                <!-- Header Form -->
                <div class="mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-white tracking-tight">{{ __('Log in to your account') }}</h1>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Enter your email and password below to log in') }}
                    </p>
                </div>

                <!-- Session Status (Pesan Error/Sukses) -->
                <x-auth-session-status class="mb-4 text-center text-sm" :status="session('status')" />

                <x-passkey-verify />

                <!-- Form Area -->
                <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                    @csrf

                    <!-- Email Address Input -->
                    <div class="space-y-1">
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
                    </div>

                    <!-- Password Input -->
                    <div class="relative space-y-1">
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
                            <flux:link class="absolute top-0 text-xs font-medium end-0 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" :href="route('password.request')" wire:navigate>
                                {{ __('Forgot password?') }}
                            </flux:link>
                        @endif
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <flux:button variant="primary" type="submit" class="w-full py-3 text-base font-semibold shadow-md shadow-indigo-500/20 transition-all duration-200" data-test="login-button">
                            {{ __('Log in') }}
                        </flux:button>
                    </div>
                </form>

                <!-- Sign Up Link Area -->
                <div class="text-center text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-8 pt-6 border-t border-zinc-200/80 dark:border-zinc-800/80">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <flux:link :href="route('register')" wire:navigate class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline ml-1">
                        {{ __('Sign up') }}
                    </flux:link>
                </div>
            </div>
        </div>

    </div>
</body>
</html>