            <div class="lg:col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($posts as $post)
                        <article wire:key="post-{{ $post->id }}"
                            class="group flex flex-col bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800/80 overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.03)] dark:shadow-none hover:shadow-[0_12px_30px_rgb(0,0,0,0.06)] hover:-translate-y-1.5 transition-all duration-300 ease-out">
                            
                            <a href="{{ route('blog.show', $post->slug) }}" wire:navigate class="relative h-60 overflow-hidden bg-zinc-100 dark:bg-zinc-800/50 block">
                                @if($post->featured_image)
                                    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}"
                                        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center transform group-hover:scale-105 transition-transform duration-700 ease-out">
                                        <span class="text-5xl text-indigo-500/80 dark:text-indigo-400 font-semibold tracking-tight">{{ substr($post->title, 0, 1) }}</span>
                                    </div>
                                @endif

                                @if($post->category)
                                    <div class="absolute top-4 left-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md text-zinc-800 dark:text-zinc-200 border border-white/20 shadow-sm">
                                            {{ $post->category->name }}
                                        </span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-6 sm:p-7 flex flex-col flex-grow">
                                <div class="flex items-center text-xs font-medium text-zinc-400 dark:text-zinc-500 mb-3.5 gap-2.5">
                                    <time datetime="{{ $post->published_at->format('Y-m-d') }}">{{ $post->published_at->format('M d, Y') }}</time>
                                    <span class="w-1 h-1 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $post->user->name }}</span>
                                    
                                    @if ($post->views_count > 0)
                                        <span class="w-1 h-1 rounded-full bg-zinc-300 dark:bg-zinc-700"></span>
                                        <span class="flex items-center gap-1.5 text-zinc-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            {{ number_format($post->views_count) }}
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3 line-clamp-2 tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    <a href="{{ route('blog.show', $post->slug) }}" wire:navigate>
                                        {{ $post->title }}
                                    </a>
                                </h2>

                                @if($post->excerpt)
                                    <p class="text-zinc-500 dark:text-zinc-400 text-sm leading-relaxed mb-6 line-clamp-3 flex-grow font-normal">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-4 border-t border-zinc-100 dark:border-zinc-800/60 flex items-center justify-between">
                                    <span class="inline-flex items-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors">
                                        Read article 
                                        <svg class="w-3.5 h-3.5 ml-1.5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center p-16 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 rounded-2xl text-center shadow-sm">
                            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100">No posts found</p>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Try adjusting your search terms or active filters.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            </div>