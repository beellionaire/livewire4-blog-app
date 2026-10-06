<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

new class extends Component
{
    use WithFileUploads;
    
    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published')]
    public string $status = 'draft';

    #[Validate('required|array|min:1')]
    public array $selectedCategories = [];
    
    #[Validate('nullable|array')]
    public array $selectedTags = [];

    public function with(): array
    {
        return [
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ];
    }

    public function save(){
        $this->validate();

        $post = new Post();
        $post->user_id = auth()->id();
        $post->title = $this->title;
        $post->slug = Str::slug($this->title);
        $post->excerpt = $this->excerpt;
        $post->content = $this->content;
        $post->status = $this->status;

        if ($this->featured_image) {
            $path = $this->featured_image->store('posts','public');
            $post->featured_image = $path;
        }

        if ($this->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        $post->categories()->attach($this->selectedCategories);

        if (!empty($this->selectedTags)) {
            $post->tags()->attach($this->selectedTags);
        }

        session()->flash('success','Post created successfully!');

        $this->redirect(route('posts.index'), navigate: true);
    }
};
?>

<div class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <div class="mb-8 w-full">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Create New Post</h1>
        <p class="mt-1 text-sm text-zinc-600">Write, format, and publish your new blog post.</p>
    </div>

    <div class="w-full bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 md:p-10 shadow-sm transition-all">
        <form wire:submit="save" class="space-y-10">
            
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Post Title
                    </label>
                    <input 
                        type="text"
                        id="title"
                        wire:model.live.debounce="title" 
                        placeholder="Enter an engaging post title"
                        autofocus
                        class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner font-medium"
                    />
                    @error('title')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Excerpt <span class="text-zinc-400 font-normal ml-1">(Optional)</span>
                    </label>
                    <textarea 
                        id="excerpt"
                        wire:model="excerpt" 
                        placeholder="A short, compelling summary of your post..."
                        rows="2"
                        class="w-full px-4 py-3 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner resize-y"
                    ></textarea>
                    @error('excerpt')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-2 text-xs font-medium text-zinc-500">This will appear in post previews and search engine results.</p>
                </div>
            </div>

            <div>
                <label for="content" class="block text-sm font-semibold text-zinc-700 mb-2">
                    Main Content
                </label>
                <div wire:ignore class="relative rounded-xl overflow-hidden shadow-inner border border-zinc-200 bg-zinc-50 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500 transition-all">
                    <input type="hidden" name="content" id="x-content">
                    <trix-editor
                        input="x-content"
                        class="trix-content w-full border-0 bg-transparent text-zinc-900 text-sm min-h-[400px] p-4 focus:outline-none prose prose-indigo max-w-none"
                        x-data
                        x-on:trix-change="$wire.content = $event.target.value"
                    ></trix-editor>
                </div>
                
                @error('content')
                    <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <hr class="border-zinc-100">

            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-3">
                    Featured Image
                </label>
                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <div class="w-full sm:w-auto flex-1">
                        <input 
                            type="file" 
                            wire:model="featured_image"
                            accept="image/*"
                            class="block w-full text-sm text-zinc-500 cursor-pointer
                                file:mr-4 file:py-2.5 file:px-5
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100 transition-all"
                        />
                        <div wire:loading wire:target="featured_image" class="mt-3 text-sm font-medium text-indigo-600 flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Uploading image...
                        </div>
                        @error('featured_image')
                            <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if ($featured_image)
                        <div class="relative flex-shrink-0" wire:transition>
                            <img src="{{ $featured_image->temporaryUrl() }}" class="h-32 w-48 object-cover rounded-2xl border border-zinc-200 shadow-sm" alt="Featured Image Preview">
                            <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-black/10"></div>
                        </div>
                    @endif
                </div>
            </div>

            <hr class="border-zinc-100">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-8">
                
                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-3">
                        Categories <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto p-1 -m-1">
                        @foreach($categories as $category)
                            <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-zinc-50/50 p-3.5 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 group items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedCategories" 
                                    value="{{ $category->id }}"
                                    class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition-colors"
                                />
                                <span class="ml-3 flex items-center flex-1 truncate">
                                    <span 
                                        class="inline-block w-3 h-3 rounded-full mr-2.5 shadow-sm flex-shrink-0" 
                                        style="background-color: {{ $category->color ?? '#6366f1' }}"
                                    ></span>
                                    <span class="text-sm font-bold text-zinc-900 group-hover:text-indigo-900 transition-colors truncate">{{ $category->name }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedCategories')
                        <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700 mb-3">
                        Tags <span class="text-zinc-400 font-normal ml-1">(Optional)</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-1 -m-1">
                        @foreach($tags as $tag)
                            <label class="relative flex cursor-pointer rounded-xl border border-zinc-200 bg-zinc-50/50 p-2.5 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 group items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedTags" 
                                    value="{{ $tag->id }}"
                                    class="h-3.5 w-3.5 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition-colors"
                                />
                                <span class="ml-2 text-xs font-semibold text-zinc-700 group-hover:text-indigo-900 transition-colors truncate">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedTags')
                        <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-3 text-xs font-medium text-zinc-500">Select relevant tags to help readers find your content.</p>
                </div>
                
            </div>

            <hr class="border-zinc-100">

            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-4">
                    Publication Status
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 group has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                        <div class="flex h-5 items-center mt-0.5">
                            <input 
                                type="radio" 
                                wire:model="status" 
                                value="draft"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 transition-colors"
                            />
                        </div>
                        <div class="ml-3 flex flex-col">
                            <span class="block text-sm font-bold text-zinc-900 transition-colors group-has-[:checked]:text-indigo-900">Save as Draft</span>
                            <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">Keep it hidden. Work on it later before publishing.</span>
                        </div>
                    </label>
                    
                    @can('publish posts')
                    <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/30 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 group has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500">
                        <div class="flex h-5 items-center mt-0.5">
                            <input 
                                type="radio" 
                                wire:model="status" 
                                value="published"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-zinc-300 transition-colors"
                            />
                        </div>
                        <div class="ml-3 flex flex-col">
                            <span class="block text-sm font-bold text-zinc-900 transition-colors group-has-[:checked]:text-emerald-900">Publish Immediately</span>
                            <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">Make this post live and visible to all readers right now.</span>
                        </div>
                    </label>
                    @endcan
                    
                </div>
                @error('status')
                    <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-zinc-100">
                <a 
                    href="{{ route('posts.index') }}" 
                    class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-200"
                    wire:navigate
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-6 py-2.5 bg-indigo-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Save & Proceed
                </button>
            </div>
            
        </form>
    </div>
</div>