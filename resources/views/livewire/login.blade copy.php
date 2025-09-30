<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {
    #[Url]
    public string $redirect_url = '';
     
    public function login()
    {
        // Always a random user to make it fun.
        $user = User::inRandomOrder()->first();

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->intended($this->redirect_url);
    }
}; ?>

<div>
    <div class="max-w-sm lg:ml-40">
        <img src="/login.png" width="200" class="mx-auto" />

        <x-form wire:submit="login">
            <x-input label="E-mail" value="random@random.com" icon="o-envelope" />
            <x-input label="Password" value="random" type="password" icon="o-key" />

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>
    </div>
</div>




<?php

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

            return redirect()->intended('/');
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
}; ?>

<div>

    <x-form wire:submit.prevent="login" class="space-y-6">
        <x-input label="E-mail" wire:model.defer="email" icon="o-envelope" inline class="w-full" />
        <x-input label="Password" wire:model.defer="password" type="password" icon="o-key" inline class="w-full" />

        <div class="flex items-center justify-end mt-6">
            {{--            <x-button --}}
            {{--                label="Create an account" --}}
            {{--                class="btn-ghost" --}}
            {{--                link="/register" --}}
            {{--            /> --}}
            <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary right-2"
                spinner="login" />
        </div>
    </x-form>
</div>

