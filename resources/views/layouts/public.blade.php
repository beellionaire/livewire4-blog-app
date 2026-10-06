<!DOCTYPE html>
{{-- 
    x-data dan x-bind:class (Alpine.js bawaan Livewire 3) digunakan untuk mengatur state Dark Mode.
    Sistem akan mengecek localStorage atau preferensi sistem operasi (OS) pengunjung.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Script untuk mencegah FOUC (Flash of Unstyled Content) saat transisi tema --}}
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>
{{-- 
    antialiased: Membuat font lebih halus (premium typography).
    bg-zinc-50 / dark:bg-zinc-950: Menggunakan warna Zinc (abu-abu kebiruan) yang lebih premium daripada warna Gray standar.
    flex flex-col min-h-screen: Memastikan footer selalu berada di paling bawah layar meskipun konten sedikit.
--}}
<body class="font-sans antialiased text-zinc-900 bg-zinc-50 dark:bg-zinc-950 dark:text-zinc-100 min-h-screen flex flex-col transition-colors duration-300 selection:bg-indigo-500 selection:text-white">

    @include('layouts.partials.navbar')

    <!-- MAIN CONTENT -->
    {{-- flex-grow memastikan area konten membesar dan mendorong footer ke paling bawah --}}
    <main class="flex-grow py-10 w-full transition-colors duration-300">
        {{ $slot }}
    </main>

    <!-- FOOTER -->
    @include('layouts.partials.footer')

</body>
</html>