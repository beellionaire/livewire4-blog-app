<div class="mt-12 border-t border-gray-200 pt-8">
    
    <!-- HEADER KOMENTAR -->
    <h2 class="text-2xl font-bold text-gray-900 mb-6">
        {{-- 
            Menghitung total seluruh komentar. 
            $comments->count() menghitung jumlah komentar utama.
            $comments->sum(...) menjumlahkan seluruh balasan dari masing-masing komentar utama.
        --}}
        Comments ({{ $comments->count() + $comments->sum(fn($c) => $c->replies->count()) }})
    </h2>

    <!-- NOTIFIKASI SUKSES (FLASH MESSAGE) -->
    {{-- Mengecek apakah ada session bernama 'comment-success' (dikirim dari fungsi postComment/postReply) --}}
    @if (session('comment-success'))
        {{-- wire:transition memberikan efek animasi (fade in/out) halus khas Livewire saat notifikasi muncul/hilang --}}
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-green-800">{{ session('comment-success') }}</p>
        </div>
    @endif

    <!-- FORM KOMENTAR UTAMA (BARU) -->
    {{-- @auth memastikan form hanya dirender jika user sudah login --}}
    @auth
        <div class="mb-8 bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave a comment</h3>
            
            {{-- wire:submit akan memanggil fungsi postComment() di class PHP saat tombol submit ditekan. 
                 Livewire otomatis mencegah form melakukan reload halaman (event.preventDefault) --}}
            <form wire:submit="postComment">
                
                {{-- wire:model mengikat input textarea ini secara real-time dengan properti $newComment di class PHP --}}
                <textarea 
                    wire:model="newComment"
                    rows="4"
                    placeholder="Share your thoughts..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
                
                {{-- Menampilkan pesan error validasi khusus untuk properti 'newComment' (jika kosong atau terlalu pendek) --}}
                @error('newComment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Post Comment
                    </button>
                </div>
            </form>
        </div>
    {{-- @else dijalankan jika pengunjung belum login --}}
    @else
        <div class="mb-8 bg-gray-50 rounded-lg p-6 text-center">
            <p class="text-gray-600 mb-4">You must be logged in to comment.</p>
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Login to Comment
            </a>
        </div>
    @endauth

    <!-- DAFTAR KOMENTAR -->
    <div class="space-y-6">
        
        {{-- @forelse adalah gabungan @foreach dan @if(empty). 
             Jika $comments ada isinya, akan dilooping. Jika kosong, akan masuk ke blok @empty di paling bawah --}}
        @forelse($comments as $comment)
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                
                <!-- BAGIAN HEADER KOMENTAR (FOTO, NAMA, WAKTU) -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        {{-- Memanggil API UI-Avatars untuk membuat foto profil otomatis berinisial nama User --}}
                        <img 
                            src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=4f46e5&color=fff" 
                            alt="{{ $comment->user->name }}" 
                            class="w-10 h-10 rounded-full mr-3"
                        >
                        <div>
                            {{-- Menampilkan nama relasi user pembuat komentar --}}
                            <p class="font-medium text-gray-900">{{ $comment->user->name }}</p>
                            {{-- diffForHumans() mengubah format waktu SQL menjadi format ramah baca (misal: "2 hours ago") --}}
                            <p class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <!-- ISI/TEKS KOMENTAR -->
                <div class="text-gray-700 mb-4">
                    {{ $comment->content }}
                </div>

                <!-- TOMBOL AKSI (REPLY / CANCEL) -->
                <div class="flex items-center gap-4">
                    {{-- Hanya tampilkan tombol Reply jika user login --}}
                    @auth
                        {{-- Mengecek properti state $replyingTo. 
                             Jika ID komentar ini sama dengan ID yang sedang diklik user untuk dibalas, tampilkan tombol "Cancel" --}}
                        @if($replyingTo === $comment->id)
                            <button wire:click="cancelReply" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancel
                            </button>
                        @else
                            {{-- wire:click="startReply(...)" memanggil fungsi startReply() di PHP 
                                 dan mengirimkan parameter ID komentar saat ini --}}
                            <button wire:click="startComment({{ $comment->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                Reply
                            </button>
                        @endif
                    @endauth
                </div>

                <!-- FORM BALASAN (REPLY FORM) -->
                {{-- Form ini hanya akan dirender (muncul) jika state $replyingTo cocok dengan ID komentar ini --}}
                @if($replyingTo === $comment->id)
                    <div class="mt-4 bg-gray-50 rounded-lg p-4" wire:transition>
                        
                        {{-- Memanggil postReply() dan mengirimkan parameter ID parent komentar --}}
                        <form wire:submit="postReply({{ $comment->id }})">
                            {{-- Mengikat input dengan properti $replyContent --}}
                            <textarea 
                                wire:model="replyContent"
                                rows="3"
                                placeholder="Write your reply..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            
                            @error('replyContent')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" wire:click="cancelReply" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Post Reply
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- DAFTAR BALASAN (NESTED REPLIES) -->
                {{-- Mengecek apakah komentar utama ini memiliki balasan --}}
                @if($comment->replies->count() > 0)
                    {{-- Styling UI menggunakan margin-left (ml-8) dan border kiri (border-l-2) 
                         untuk memberikan efek visual menjorok ke dalam (nested) --}}
                    <div class="mt-6 ml-8 space-y-4 border-l-2 border-gray-200 pl-6">
                        
                        {{-- Melakukan looping (iterasi) terhadap data balasan --}}
                        @foreach($comment->replies as $reply)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start mb-3">
                                    <img 
                                        src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&background=6366f1&color=fff" 
                                        alt="{{ $reply->user->name }}" 
                                        class="w-8 h-8 rounded-full mr-3"
                                    >
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $reply->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="text-gray-700 text-sm">
                                    {{ $reply->content }}
                                </div>
                            </div>
                        @endforeach
                        
                    </div>
                @endif

            </div>
        {{-- Jika tidak ada satupun komentar di artikel ini, tampilkan blok @empty di bawah ini --}}
        @empty
            <div class="text-center py-12">
                <p class="text-gray-500">No comments yet. Be the first to share your thoughts!</p>
            </div>
        @endforelse
    </div>
</div>