<div class="py-12 bg-zinc-50 dark:bg-zinc-950 min-h-screen transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- HEADER SECTION -->
        @include('livewire.blog.hero')

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
            
            @include('livewire.blog.sidebar')

            <!-- POSTS GRID -->
            @include('livewire.blog.content')
            
        </div>

        <!-- Subscriber Section -->
        <div class="mt-20 border-t border-zinc-200 dark:border-zinc-800 pt-16">
           <livewire:blog.subscribe-wrapper/>
        </div>

    </div>
</div>