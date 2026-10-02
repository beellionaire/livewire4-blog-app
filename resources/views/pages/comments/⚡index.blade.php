<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Comment;

// Ini adalah format komponen Livewire 3 (biasanya digunakan di Livewire Volt atau single-file component)
new class extends Component
{
    // Menggunakan trait ini agar pagination bekerja secara dinamis tanpa reload halaman
    use WithPagination;
    
    // Properti publik yang otomatis terikat (data binding) dengan input di frontend
    public string $search = '';
    public string $statusFilter = 'all';

    /**
     * Fungsi with() digunakan di Livewire 3 untuk mengirim data ke view (pengganti fungsi render()).
     * Data yang dikirim dari sini tidak akan disimpan di memori state, sehingga lebih ringan.
     */
    public function with(): array
    {
        // Memulai query ke model Comment, mengambil relasi 'user' dan 'post' sekaligus (Eager Loading) 
        // untuk mencegah N+1 Query problem. Diurutkan dari yang terbaru (latest).
        $query = Comment::with(['user', 'post'])
            ->latest();

        // LOGIKA FILTER 1: Pencarian teks
        if ($this->search) {
            // Mencari komentar yang isi kontennya mirip dengan teks pencarian
            $query->where('content', 'like', '%' . $this->search . '%')
                  // ATAU mencari komentar berdasarkan nama user yang membuat komentar
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        }

        // LOGIKA FILTER 2: Status (Semua, Disetujui, Menunggu, Spam)
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // LOGIKA FILTER 3: Hak Akses (Role)
        // Jika user yang login adalah seorang 'author' (penulis), dia hanya boleh melihat 
        // komentar yang ada pada artikel/postingan miliknya sendiri.
        if (auth()->user()->hasRole('author')) {
            $query->whereHas('post', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Mengembalikan data $comments yang sudah difilter dan dipaginasi (20 data per halaman)
        return [
            'comments' => $query->paginate(20),
        ];
    }

    /**
     * Lifecycle Hook Livewire:
     * Fungsi ini akan otomatis dipanggil TEPAT SEBELUM nilai $search berubah.
     * Tujuannya: Jika user sedang di halaman 5 lalu mengetik pencarian baru, 
     * halaman akan direset ke halaman 1 agar data pencarian bisa terlihat.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Sama seperti di atas, reset ke halaman 1 jika user mengganti dropdown status.
     */
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Fungsi aksi untuk mengubah status komentar menjadi 'approved'.
     * Menggunakan Route Model Binding (Livewire otomatis mencari data Comment berdasarkan ID yang dikirim dari view).
     */
    public function approveComment(Comment $comment): void
    {
        $comment->update(['status' => 'approved']);
        session()->flash('success', 'Comment approved!'); // Mengirim pesan sukses sementara
    }

    /**
     * Fungsi aksi untuk menandai komentar sebagai spam.
     */
    public function markAsSpam(Comment $comment): void
    {
        $comment->update(['status' => 'spam']);
        session()->flash('success', 'Comment marked as spam!');
    }

    /**
     * Fungsi aksi untuk menghapus komentar dari database.
     */
    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
        session()->flash('success', 'Comment deleted!');
    }
};
?>

<div>
    <!-- Bagian Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Comments</h1>
        <p class="mt-1 text-sm text-gray-600">Moderate and manage post comments</p>
    </div>

    <!-- Bagian Filter Pencarian dan Status -->
    <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            
            <!-- Input Pencarian -->
            <div class="flex-1">
                <input 
                    type="text"
                    {{-- 
                        wire:model.live: Mengirim data ke backend setiap kali user mengetik.
                        .debounce.300ms: Menunggu 300 milidetik setelah user berhenti mengetik 
                        sebelum mengirim request ke server. Ini mencegah server overload (lag). 
                    --}}
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search comments..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <!-- Dropdown Filter Status -->
            <div class="sm:w-48">
                {{-- wire:model.live akan langsung memicu update data saat user memilih opsi baru di dropdown --}}
                <select 
                    wire:model.live="statusFilter" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="all">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="spam">Spam</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Menampilkan Pesan Sukses (Flash Message) -->
    @if (session('success'))
        {{-- wire:transition memberikan efek animasi muncul/menghilang yang halus --}}
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4" wire:transition>
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Daftar Komentar -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-200">
            
            {{-- Melakukan looping data komentar. Jika data kosong, akan masuk ke blok @empty --}}
            @forelse($comments as $comment)
                {{-- 
                    wire:key SANGAT PENTING dalam looping Livewire. 
                    Ini membantu Livewire melacak elemen mana yang berubah, dihapus, atau ditambah 
                    tanpa harus me-render ulang seluruh daftar (mencegah bug visual).
                --}}
                <div class="p-6 hover:bg-gray-50" wire:key="comment-{{ $comment->id }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <!-- Avatar User (Otomatis dari inisial nama) -->
                            <img 
                                src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=4f46e5&color=fff" 
                                alt="{{ $comment->user->name }}" 
                                class="w-10 h-10 rounded-full mr-3"
                            >
                            <div>
                                <!-- Nama pengirim komentar -->
                                <p class="font-medium text-gray-900">{{ $comment->user->name }}</p>
                                <p class="text-sm text-gray-500">
                                    <!-- Menampilkan Judul Artikel (dibatasi 40 huruf) dan link ke artikel tsb -->
                                    on <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">{{ Str::limit($comment->post->title, 40) }}</a>
                                </p>
                            </div>
                        </div>

                        <!-- Badge Label Status Komentar (Warna berubah sesuai status) -->
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $comment->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $comment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $comment->status === 'spam' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($comment->status) }}
                        </span>
                    </div>

                    <!-- Isi Text Komentar -->
                    <div class="text-gray-700 mb-3">
                        {{ $comment->content }}
                    </div>

                    <div class="flex items-center justify-between">
                        <!-- Tanggal Komentar Dibuat -->
                        <p class="text-sm text-gray-500">
                            {{ $comment->created_at->format('M d, Y \a\t g:i A') }}
                        </p>

                        <!-- Tombol Aksi Moderasi -->
                        <div class="flex gap-2">
                            <!-- Sembunyikan tombol Approve jika statusnya sudah approved -->
                            @if($comment->status !== 'approved')
                                {{-- Memanggil fungsi backend approveComment() dengan parameter ID komentar --}}
                                <button 
                                    wire:click="approveComment({{ $comment->id }})"
                                    class="text-sm text-green-600 hover:text-green-800 font-medium"
                                >
                                    Approve
                                </button>
                            @endif

                            <!-- Sembunyikan tombol Spam jika statusnya sudah spam -->
                            @if($comment->status !== 'spam')
                                <button 
                                    wire:click="markAsSpam({{ $comment->id }})"
                                    class="text-sm text-orange-600 hover:text-orange-800 font-medium"
                                >
                                    Mark as Spam
                                </button>
                            @endif

                            <!-- Tombol Delete (Selalu Tampil) -->
                            <button 
                                wire:click="deleteComment({{ $comment->id }})"
                                {{-- 
                                    wire:confirm adalah fitur bawaan Livewire 3 untuk memunculkan 
                                    popup konfirmasi browser bawaan (alert dialog) sebelum aksi dieksekusi. 
                                --}}
                                wire:confirm="Are you sure you want to delete this comment?"
                                class="text-sm text-red-600 hover:text-red-800 font-medium"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Tampilan jika tidak ada data komentar yang ditemukan -->
                <div class="p-12 text-center text-gray-500">
                    No comments found.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Menampilkan navigasi halaman (Pagination links) -->
    <div class="mt-6">
        {{ $comments->links() }}
    </div>
</div>