            <aside class="lg:col-span-1 space-y-6">
                
                <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-colors">
                    <label class="block text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase mb-2.5">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="Search posts..."
                            class="w-full pl-10 pr-4 py-2.5 bg-zinc-50/80 dark:bg-zinc-950/80 border border-zinc-200/60 dark:border-zinc-800/60 rounded-xl text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 shadow-inner" 
                        />
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-colors">
                    <h3 class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase mb-3">Categories</h3>
                    <div class="space-y-1">
                        <button wire:click="$set('selectedCategory', '')"
                            class="w-full text-left px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 {{ $selectedCategory === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                            All Categories
                        </button>
                        
                        @foreach($categories as $category)
                            <button wire:click="$set('selectedCategory', '{{ $category->slug }}')"
                                class="w-full text-left px-3.5 py-2 rounded-xl text-sm font-medium flex items-center justify-between transition-all duration-200 {{ $selectedCategory === $category->slug ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-sm' : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100/60 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                                <span class="flex items-center gap-2.5">
                                    <span class="inline-block w-2 h-2 rounded-full shadow-sm"
                                        style="background-color: {{ $category->color }}"></span>
                                    <span class="truncate">{{ $category->name }}</span>
                                </span>
                                <span class="text-[11px] py-0.5 px-2 rounded-full {{ $selectedCategory === $category->slug ? 'bg-white/20 text-white dark:bg-zinc-900/20 dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $category->posts_count }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white/70 dark:bg-zinc-900/70 backdrop-blur-xl p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-colors">
                    <h3 class="text-xs font-semibold tracking-wider text-zinc-400 dark:text-zinc-500 uppercase mb-3">Popular Tags</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($tags as $tag)
                            @if($tag->posts_count > 0)
                                <button wire:click="$set('selectedTag', '{{ $tag->slug }}')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 border {{ $selectedTag === $tag->slug ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm shadow-indigo-500/20' : 'bg-zinc-50/60 dark:bg-zinc-800/40 border-zinc-200/60 dark:border-zinc-700/60 text-zinc-600 dark:text-zinc-400 hover:border-zinc-300 dark:hover:border-zinc-600 hover:text-zinc-900 dark:hover:text-zinc-200' }}">
                                    {{ $tag->name }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if($search || $selectedCategory || $selectedTag)
                    <button wire:click="clearFilters" wire:transition
                        class="w-full px-4 py-2.5 bg-rose-50/80 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold transition-colors flex items-center justify-center gap-2 border border-rose-200/50 dark:border-rose-500/10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Clear All Filters
                    </button>
                @endif
            </aside>