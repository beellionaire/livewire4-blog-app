<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="font-sans antialiased text-zinc-900 bg-zinc-50 dark:bg-zinc-950 dark:text-zinc-100 min-h-screen flex flex-col transition-colors duration-300 selection:bg-indigo-500 selection:text-white">

    @include('layouts.partials.navbar')

  
    <main class="flex-grow py-10 w-full transition-colors duration-300">
        {{ $slot }}
    </main>

    @include('layouts.partials.footer')

</body>
</html>