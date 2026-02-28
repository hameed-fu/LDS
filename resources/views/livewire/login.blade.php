<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

new #[Layout('components.layouts.guest')] #[Title('Login')] class extends Component {
    use Toast;

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            return $user->role == 'admin'
                ? redirect()->route('dashboard')
                : redirect()->route('home');
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();

            $user = auth()->user();

            return $user->role === 'admin'
                ? redirect()->route('dashboard')
                : redirect()->route('home');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};

?>

<div class=" flex items-center justify-center bg-gradient-to-br   p-4">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md border border-blue-100">

        <div class="text-center mb-6">
            <img src="/login.png" class="mx-auto w-24 mb-2" />
            <h1 class="text-xl font-semibold text-gray-700">Welcome Back</h1>
            <p class="text-gray-500 text-sm">Please login to continue</p>
        </div>

        <x-form wire:submit="login" class="space-y-4">
            <x-input 
                label="E-mail" 
                wire:model.defer="email" 
                icon="o-envelope" 
                inline 
                class="w-full"
            />

            <x-input 
                label="Password" 
                wire:model.defer="password" 
                type="password" 
                icon="o-key" 
                inline 
                class="w-full"
            />

            <x-slot:actions>
                <x-button 
                    label="Login" 
                    type="submit" 
                    icon="o-paper-airplane" 
                    class="btn-primary w-full" 
                    spinner="login" 
                />
            </x-slot:actions>
        </x-form>

    </div>

</div>
