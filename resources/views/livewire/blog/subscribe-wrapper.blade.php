<div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-950 to-zinc-950 rounded-3xl p-8 md:p-12 border border-indigo-500/20 shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
    <!-- Ambient background glow (SaaS aesthetic) -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-xl mx-auto text-center">
        <!-- Title & Description -->
        <h2 class="text-2xl md:text-3xl font-bold text-white tracking-tight mb-3">Stay updated</h2>
        <p class="text-indigo-200/80 text-sm md:text-base mb-8 font-normal">
            Get notified when we publish new posts, straight to your inbox.
        </p>

        <!-- Success Message -->
        @if (session('subscribe-success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm font-medium backdrop-blur-md shadow-sm" wire:transition>
                {{ session('subscribe-success') }}
            </div>
        @endif

        <!-- Form -->
        <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input 
                    type="email" 
                    wire:model="email" 
                    placeholder="Enter your email address" 
                    class="w-full px-4 py-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-white placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30 transition-all shadow-inner"
                >
            </div>

            <button 
                type="submit" 
                class="px-6 py-3.5 bg-white text-indigo-950 hover:bg-indigo-50 font-semibold rounded-xl text-sm transition-all duration-200 shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap"
            >
                Subscribe
            </button>
        </form>

        <!-- Error Message -->
        @error('email')
            <p class="mt-3 text-xs text-rose-300 text-left font-medium pl-1">{{ $message }}</p>            
        @enderror
    </div>
</div>