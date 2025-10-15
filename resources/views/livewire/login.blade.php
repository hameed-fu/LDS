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
            if (auth()->user()->role == 'admin') {
                return redirect(route('dashboard'));
            } else {
                return redirect(route('/'));
            }
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();

            $user = auth()->user();

            return $user->role === 'admin' ? redirect()->route('dashboard') : redirect()->route('home');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
};

?>

<div>
    <div class="ma ">
        <img src="/login.png" width="200" class="mx-auto" />

        <x-form wire:submit="login">
            <x-input label="E-mail" wire:model.defer="email" icon="o-envelope" inline class="w-full" />
            <x-input label="Password" wire:model.defer="password" type="password" icon="o-key" inline class="w-full" />

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>
    </div>
</div>
