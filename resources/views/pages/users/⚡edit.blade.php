<?php

use Livewire\Component;
use App\Models\User;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public User $user;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $email = '';

    #[Validate('nullable|string|min:8')]
    public string $password = '';

    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|string|min:8',
            'selectedRoles' => 'required|array|min:1',
        ];
    }

     public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    public function update(): void
    {
        $this->validate();

        $this->user->name = $this->name;
        $this->user->email = $this->email;
        
        if ($this->password) {
            $this->user->password = Hash::make($this->password);
        }

        $this->user->save();

        $this->user->syncRoles($this->selectedRoles);

        session()->flash('success', 'User updated successfully!');

        $this->redirect(route('users.index'), navigate: true);
    }
     
};
?>

<div class="min-h-screen bg-white text-zinc-900 p-6 transition-colors duration-200">
    
    <!-- Header Full Width -->
    <div class="mb-8 w-full">
        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Edit User</h1>
        <p class="mt-1 text-sm text-zinc-600">Update user information and assign their roles.</p>
    </div>

    <!-- Form Container Full Width -->
    <div class="w-full bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 shadow-sm transition-all">
        <form wire:submit="update" class="space-y-8">
            
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
                        New Password
                    </label>
                    <input 
                        type="password"
                        id="password"
                        wire:model="password" 
                        placeholder="Leave blank to keep current password"
                        class="w-full px-4 py-2.5 rounded-xl bg-zinc-50 border border-zinc-200 text-zinc-900 placeholder-zinc-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner"
                    />
                    @error('password')
                        <p class="mt-2 text-sm font-medium text-rose-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-2 text-xs font-medium text-zinc-500">Leave blank to keep current password.</p>
                </div>
            </div>

            <!-- Separator -->
            <hr class="border-zinc-100">

            <!-- Roles (Responsive Full Width Grid) -->
            <div>
                <label class="block text-sm font-semibold text-zinc-700 mb-4">
                    Assign Roles
                </label>
                
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
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Update User
                </button>
            </div>
            
        </form>
    </div>
</div>