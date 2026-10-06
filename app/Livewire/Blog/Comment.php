<?php

namespace App\Livewire\Blog;

// Menggunakan alias 'ModelsComment' untuk App\Models\Comment.
// Ini WAJIB dilakukan karena nama class Livewire ini juga 'Comment'.
// Jika tidak di-alias, PHP akan bingung membedakan antara Model dan Component.
use App\Models\Comment as ModelsComment;
use App\Models\Post;
use App\Notifications\NewCommentNotification;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Comment extends Component
{
    // Menyimpan data Postingan yang sedang dilihat
    public Post $post;

    /**
     * #[Validate] adalah fitur Livewire 3 untuk validasi properti secara otomatis.
     * Properti $newComment terikat dengan input form komentar utama via wire:model.
     */
    #[Validate('required|string|min:3|max:1000')]
    public string $newComment = '';

    // Menyimpan ID komentar yang sedang dibalas.
    // Bernilai null jika user tidak sedang membalas siapa-siapa.
    // Properti ini mengatur muncul/tidaknya form balasan di tampilan (UI).
    public ?int $replyingTo = null;

    // Terikat dengan input form untuk membalas komentar via wire:model.
    #[Validate('required|string|min:3|max:1000')]
    public string $replyContent = '';

    /**
     * Method mount() berjalan pertama kali saat komponen dipanggil di Blade.
     * Menerima parameter data Postingan dari parent view (misal: <livewire:blog.comments :post="$post" />).
     */
    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * Method untuk mengirim komentar utama (Top Level Comment).
     *
     * Catatan Developer: Type hint ': RedirectResponse' di sini berpotensi memunculkan error PHP
     * jika user sedang login, karena di akhir kode fungsi ini tidak me-return apapun (void).
     * Sebaiknya dihapus atau ubah menjadi tipe data gabungan/void jika menggunakan PHP 8+.
     */
    public function postComment() // <- Saran: Hapus ': RedirectResponse'
    {// Pengecekan: Jika user belum login, lemparkan ke halaman login
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Memvalidasi input $newComment berdasarkan aturan #[Validate] di atas
        $this->validate(['newComment' => 'required|string|min:3|max:1000']);

        // Menyimpan data komentar utama ke database
        $comment = ModelsComment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'content' => $this->newComment,
            'status' => 'approved',
        ]);

        // Mengosongkan form input setelah berhasil
        $this->newComment = '';

        // tambahkan notifikasi disini
        if ($this->post->user_id !== auth()->id()) {
            $this->post->user->notify(new NewCommentNotification($comment));
        }

        // Memancarkan sinyal (event) 'comment-posted' ke aplikasi.
        // Tujuannya agar fungsi render() memuat ulang daftar komentar secara instan.
        $this->dispatch('comment-posted');

        // Mengirim pesan sukses ke session agar bisa ditampilkan sebagai notifikasi (Toast/Alert)
        session()->flash('comment-success', 'Komentar berhasil ditambahkan');
    }

    /**
     * Mengatur state saat tombol "Reply/Balas" diklik oleh user.
     */
    public function startComment($commentId) // <- Saran: Hapus ': RedirectResponse'
    {// Cegah user guest untuk membuka form balasan
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // Mengisi nilai replyingTo agar form balasan muncul di bawah komentar spesifik tersebut
        $this->replyingTo = $commentId;
        $this->replyContent = ''; // Pastikan form balasan dalam keadaan kosong
    }

    /**
     * Membatalkan balasan (menutup form balasan).
     */
    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->replyContent = '';
    }

    /**
     * Method untuk mengirim balasan (Reply/Nested Comment).
     * Menerima parameter $parentId yang merupakan ID dari komentar yang dibalas.
     */
    public function postReply($parentId) // <- Saran: Hapus ': RedirectResponse'
    {if (! auth()->check()) {
        return redirect()->route('login');
    }

        // Karena form balasan memiliki properti tersendiri, kita memvalidasi
        // properti $replyContent secara manual (tidak memanggil $this->validate() global)
        $this->validate(['replyContent' => 'required|string|min:3|max:1000']);

        // Menyimpan balasan ke database
        $comment = ModelsComment::create([
            'post_id' => $this->post->id,
            'user_id' => auth()->id(),
            'parent_id' => $parentId, // Ini membedakan balasan dengan komentar utama
            'content' => $this->replyContent,
            'status' => 'approved',
        ]);

        // Menutup form dan mengosongkan isi input balasan
        $this->replyingTo = null;
        $this->replyContent = '';

        // notify post author
        if ($this->post->user_id !== auth()->id()) {
            $this->post->user->notify(new NewCommentNotification($comment));
        }

        // Memancarkan sinyal agar tampilan dirender ulang
        $this->dispatch('comment-posted');

        // Menyiapkan pesan notifikasi
        session()->flash('comment-success', 'Komentar berhasil ditambahkan');
    }

    /**
     * Mengambil daftar komentar dan merender file View (HTML).
     *
     * Attribute #[On('comment-posted')] membuat fungsi render() mendengarkan (listen)
     * sinyal 'comment-posted' yang kita tembakkan dari dispatch() di atas.
     * Jika sinyal tersebut tertangkap, Livewire otomatis memanggil ulang render()
     * untuk menampilkan komentar baru tanpa perlu memuat ulang seluruh halaman browser.
     */
    #[On('comment-posted')]
    public function render()
    {
        $comments = ModelsComment::where('post_id', $this->post->id)
            ->approved() // Memanggil Local Scope: Status harus 'approved'
            ->topLevel() // Memanggil Local Scope: Hanya ambil komentar utama (parent_id = null)
            ->with(['user', 'replies.user']) // Eager Loading: Ambil data user, data balasan, dan data user dari balasan
            ->latest() // Urutkan dari komentar terbaru ke terlama (DESC)
            ->get();

        // Mengirim variabel $comments ke file resources/views/livewire/blog/comment.blade.php
        return view('livewire.blog.comment', [
            'comments' => $comments,
        ]);
    }
}
