<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    /**
     * Inicializa o componente com os dados do usuário autenticado.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Atualiza as informações de perfil.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Envia a notificação de verificação de e-mail.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    {{-- Cabeçalho padronizado --}}
    @include('partials.settings-heading')

    {{--
        Chamada do layout usando o componente anônimo.
        Isso evita que o Laravel procure o namespace 'layouts::' que causava erro.
    --}}
    <x-settings.layout :heading="__('Perfil')" :subheading="__('Atualize suas informações pessoais e endereço de e-mail.')">

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Campo Nome --}}
            <flux:input
                wire:model="name"
                :label="__('Nome')"
                type="text"
                required
                autofocus
                autocomplete="name"
            />

            {{-- Campo E-mail --}}
            <div class="space-y-2">
                <flux:input
                    wire:model="email"
                    :label="__('E-mail')"
                    type="email"
                    required
                    autocomplete="username"
                />

                {{-- Verificação de E-mail (Apenas se o Model implementar MustVerifyEmail) --}}
                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/50 dark:bg-yellow-950/20">
                        <flux:text size="sm" class="text-yellow-700 dark:text-yellow-400">
                            {{ __('Seu endereço de e-mail não está verificado.') }}

                            <button type="button" wire:click.prevent="resendVerificationNotification" class="ms-1 font-bold underline hover:text-yellow-800 dark:hover:text-yellow-300">
                                {{ __('Reenviar link de verificação.') }}
                            </button>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text size="sm" class="mt-2 font-medium text-green-600 dark:text-green-400">
                                {{ __('Um novo link foi enviado para seu e-mail.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Botão de Ação --}}
            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="px-8">
                    {{ __('Salvar Alterações') }}
                </flux:button>

                <x-action-message on="profile-updated">
                    {{ __('Salvo com sucesso.') }}
                </x-action-message>
            </div>
        </form>

        <section class="mt-10 border-t border-zinc-200 pt-10 dark:border-zinc-700">
            <flux:heading size="lg">Equipes</flux:heading>

            <div class="mt-4 space-y-3">
                @forelse (auth()->user()->equipes()->orderBy('nom_equipe')->get() as $equipe)
                    <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $equipe->nom_equipe }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $equipe->movimento?->des_sigla }}</div>
                        </div>

                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ $equipe->pivot->papel->label() }}
                        </span>
                    </div>
                @empty
                    <flux:text>Voce ainda nao possui equipes atribuidas.</flux:text>
                @endforelse
            </div>
        </section>

        <div class="mt-10 border-t border-zinc-200 pt-10 dark:border-zinc-700">
            <livewire:settings.delete-user-form />
        </div>

    </x-settings.layout>
</section>
