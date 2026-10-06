<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->selectedRoles);

        session()->flash('success', 'User created successfully!');
        
        $this->redirect(route('users.index'), navigate: true);
    }
};
?>

<div class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <!-- Header Full Width -->
    <div class="mb-8 w-full">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Create New User</h1>
        <p class="mt-1 text-sm text-zinc-600">Add a new user to the system and assign their roles.</p>
    </div>

    <!-- Form Container Full Width -->
    <div class="w-full bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 shadow-sm transition-all">
        <form wire:submit="save" class="space-y-8">
            
            <!-- Grid Layout for Inputs -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Name -->
                <div class="lg:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Name
                    </label>
                    <input 
                        type="text"
                        id="name"
                        wire:model="name" 
                        placeholder="Enter user's full name"
                        autofocus
                        class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                    />
                    @error('name')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email"
                        id="email"
                        wire:model="email" 
                        placeholder="user@example.com"
                        class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                    />
                    @error('email')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-zinc-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password"
                        id="password"
                        wire:model="password" 
                        placeholder="Minimum 8 characters"
                        class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                    />
                    @error('password')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Separator -->
            <hr class="border-zinc-100">

            <!-- Roles (Responsive Full Width Grid) -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-4">
                    Assign Roles
                </label>
                
                <!-- Grid berubah menjadi 3 atau 4 kolom di layar lebar agar rapi pada full-width -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($roles as $role)
                        <label class="relative flex cursor-pointer rounded-2xl border border-zinc-200 bg-zinc-50/50 p-4 shadow-sm hover:border-indigo-300 hover:bg-indigo-50/50 transition-colors focus-within:ring-2 focus-within:ring-indigo-500/20 group">
                            <div class="flex h-5 items-center mt-0.5">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedRoles" 
                                    value="{{ $role->name }}"
                                    class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 transition-colors"
                                />
                            </div>
                            <div class="ml-3 flex flex-col">
                                <span class="block text-sm font-bold text-zinc-900 group-hover:text-indigo-900 transition-colors">{{ ucfirst($role->name) }}</span>
                                <span class="block text-xs text-zinc-500 mt-1 leading-relaxed">{{ $role->description ?? 'No description provided for this role.' }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('selectedRoles')
                    <p class="mt-3 text-sm font-medium text-rose-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a 
                    href="{{ route('users.index') }}" 
                    class="px-5 py-2.5 bg-white border border-zinc-200 rounded-xl text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-200"
                    wire:navigate
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl text-sm font-semibold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Create User
                </button>
            </div>
            
        </form>
    </div>
</div>