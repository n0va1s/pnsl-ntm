<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Esqueceu sua senha?')" :description="__('Digite seu e-mail para receber um link de redefinição de senha')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('E-mail')"
            type="email"
            required
            autofocus
            placeholder="email@exemplo.com"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Enviar link de redefinição de senha') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('Ou, retornar para') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Entrar') }}</flux:link>
    </div>
</div>
