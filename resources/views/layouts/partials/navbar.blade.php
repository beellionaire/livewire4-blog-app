
<div class="fixed top-4 inset-x-0 z-50 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <nav class="bg-white/80 dark:bg-zinc-950/85 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 rounded-full px-6 shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition-all duration-300">
        <div class="flex justify-between items-center h-16">
            

            <div class="flex items-center">
                <a href="{{ route('blog.index') }}" class="flex items-center gap-2 group" wire:navigate>
                    <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        {{ config('app.name') }}
                    </span>
                </a>
            </div>
          
            <div class="flex items-center gap-3 sm:gap-4">
                
               
                <button @click="darkMode = !darkMode" type="button" class="p-2 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
           
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                  
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

              
                <div class="w-px h-5 bg-zinc-200 dark:bg-zinc-700"></div>

                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white transition-colors" wire:navigate>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2 text-sm font-medium text-white bg-zinc-900 dark:bg-white dark:text-zinc-900 rounded-full hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-all shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-900 dark:focus:ring-offset-zinc-950" wire:navigate>
                        Log in
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</div>