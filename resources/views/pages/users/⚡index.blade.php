<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';

    public function with(): array
    {
        $query = User::with('roles')->latest();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        return [
            'users' => $query->paginate(10),
            'roles' => Role::all(),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUser(User $user): void
    {
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account!');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');
    }
};
?>

<div x-data="{ showDeleteModal: false, userIdToDelete: null }" class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Users</h1>
        <p class="mt-1 text-sm text-zinc-600">Manage user accounts and roles</p>
    </div>

    <div class="mb-6 bg-white rounded-2xl border border-zinc-200 p-5 shadow-sm transition-all">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input 
                    type="text"
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search users..." 
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                />
            </div>

            <div class="sm:w-48">
                <select 
                    wire:model.live="roleFilter" 
                    class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                >
                    <option value="all">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-indigo-500/20 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New User
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-sm" wire:transition>
            <p class="text-sm font-medium text-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-rose-50 border border-rose-200 rounded-2xl p-4 shadow-sm" wire:transition>
            <p class="text-sm font-medium text-rose-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </p>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-sm transition-all">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50/70">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">User</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Email</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Roles</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-zinc-400">Joined</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-zinc-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <img class="h-10 w-10 rounded-full object-cover shadow-sm border border-zinc-100" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff" alt="{{ $user->name }}">
                                    <div class="text-sm font-semibold text-zinc-900">{{ $user->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-zinc-600">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <span class="px-2.5 py-1 inline-flex text-[11px] leading-none font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span class="px-2.5 py-1 inline-flex text-[11px] leading-none font-semibold rounded-full bg-zinc-100 text-zinc-600 border border-zinc-200">
                                            No role
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-zinc-500 font-medium">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                        Edit
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <button 
                                            @click="userIdToDelete = {{ $user->id }}; showDeleteModal = true"
                                            class="text-rose-600 hover:text-rose-800 font-semibold transition-colors"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm text-zinc-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

    <div 
        x-show="showDeleteModal" 
        style="display: none;" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
        <div 
            x-show="showDeleteModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDeleteModal = false"
            class="fixed inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"
        ></div>

        <div 
            x-show="showDeleteModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @keydown.escape.window="showDeleteModal = false"
            class="relative w-full max-w-md bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-zinc-200 p-6 sm:p-8 transform transition-all"
        >
            <div class="flex items-start gap-4 sm:gap-5">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1 mt-1">
                    <h3 class="text-lg font-bold text-zinc-900 tracking-tight">Delete User</h3>
                    <p class="mt-2 text-sm text-zinc-500 leading-relaxed">
                        Are you sure you want to delete this user? All of their data will be permanently removed. This action cannot be undone.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button 
                    type="button" 
                    @click="showDeleteModal = false"
                    class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-200"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    @click="$wire.deleteUser(userIdToDelete); showDeleteModal = false"
                    class="px-5 py-2.5 bg-rose-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-md shadow-rose-500/20 hover:bg-rose-700 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                >
                    Confirm Delete
                </button>
            </div>
        </div>
    </div>

</div>