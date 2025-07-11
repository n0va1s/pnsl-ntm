<x-layouts.app :title="'Inscrição de Trabalhador'">
    <h2 id="titulo-pagina" class="text-2xl font-bold mb-6">Inscrever Trabalhador</h2>

    <form method="POST"
          action="{{ $trabalhador->exists ? route('trabalhadores.update', ['idt_pessoa' => $trabalhador->idt_pessoa]) : route('trabalhadores.store') }}"
          class="space-y-6" novalidate>
        @csrf
        @if ($trabalhador->exists)
            @method('PUT')
        @endif

        {{-- Nome Completo --}}
        <div>
            <label for="nom_pessoa" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nome Completo <span class="text-red-600">*</span>
            </label>
            <input type="text"
               id="nom_pessoa"
               name="nom_pessoa"
               value="{{ old('nom_pessoa', $trabalhador->pessoa->nom_pessoa ?? '') }}"
               placeholder="Nome completo"
               class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-zinc-800 opacity-50"
               readonly
               disabled
               {{-- Desbloqueado para teste --}}
               >
            {{-- Campo hidden para enviar o valor quando o campo estiver desabilitado --}}
            <input type="hidden" name="nom_pessoa" value="{{ old('nom_pessoa', $trabalhador->pessoa->nom_pessoa ?? '') }}">
            @error('nom_pessoa')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Telefone --}}
        <div>
            <label for="tel_pessoa" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Telefone <span class="text-red-600">*</span>
            </label>
            <input type="tel"
               id="tel_pessoa"
               name="tel_pessoa"
               maxlength="11"
               value="{{ old('tel_pessoa', $trabalhador->pessoa->tel_pessoa ?? '') }}"
               class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 dark:bg-zinc-800 opacity-50"
               placeholder="DDD + número (somente números)"
               readonly
               disabled
               {{-- Desbloqueado para teste --}}
               >
            {{-- Campo hidden para enviar o valor quando o campo estiver desabilitado --}}
            <input type="hidden" name="tel_pessoa" value="{{ old('tel_pessoa', $trabalhador->pessoa->tel_pessoa ?? '') }}">
            @error('tel_pessoa')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Evento --}}
        <div>
            <label for="idt_evento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Evento <span class="text-red-600">*</span>
            </label>
            <select
                id="idt_evento"
                name="idt_evento"
                required
                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100"
            >
                <option value="">Selecione um evento</option>
                @foreach ($eventos as $evento)
                    <option value="{{ $evento->idt_evento }}"
                        {{ old('idt_evento', $trabalhador->evento->idt_evento ?? '') == $evento->idt_evento ? 'selected' : '' }}>
                        {{ $evento->des_evento }}
                    </option>
                @endforeach
            </select>
            @error('idt_evento')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>


        {{-- Equipe de Interesse --}}
        <div>
            <label for="equipes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Equipe(s) de Interesse (máximo 3)
            </label>

            @php
            $equipesOrdenadas = $equipes->sortBy('des_grupo')->values();
            $colunas = 3;
            $porColuna = ceil($equipesOrdenadas->count() / $colunas);
            @endphp

            <div class="flex flex-wrap gap-4">
            @for ($col = 0; $col < $colunas; $col++)
                <div class="flex flex-col">
                @for ($row = 0; $row < $porColuna; $row++)
                    @php
                    $idx = $col * $porColuna + $row;
                    if ($idx >= $equipesOrdenadas->count()) break;
                    $equipe = $equipesOrdenadas[$idx];
                    @endphp
                    <div class="mb-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="equipes[]" value="{{ $equipe->des_grupo }}"
                        class="checkbox-equipe"
                        {{ in_array($equipe->des_grupo, old('equipes', $trabalhador->equipes ?? [])) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-900 dark:text-gray-100">{{ $equipe->des_grupo }}</span>
                    </label>
                    </div>
                @endfor
                </div>
            @endfor
            </div>

            @error('equipes')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div
            x-data="{ show: false, message: '' }"
            x-show="show"
            x-transition
            x-init="
            window.addEventListener('notificacao-equipes', e => {
                message = e.detail;
                show = true;
                setTimeout(() => show = false, 3000);
            });
            "
            class="fixed top-6 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-md text-white font-semibold shadow-lg flex items-center gap-2 bg-red-600"
            role="alert"
            aria-live="assertive"
            style="display: none;"
        >
            <x-heroicon-o-x-circle class="w-6 h-6 text-white" />
            <span x-text="message"></span>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.checkbox-equipe');
            const maxSelecionadas = 3;

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                const checkedBoxes = document.querySelectorAll('.checkbox-equipe:checked');
                if (checkedBoxes.length > maxSelecionadas) {
                    cb.checked = false;
                    window.dispatchEvent(new CustomEvent('notificacao-equipes', {
                    detail: `Você pode selecionar no máximo ${maxSelecionadas} equipes.`
                    }));
                }
                });
            });
            });
        </script>

         {{-- Habilidades --}}
         <div>
            <label for="des_habilidades" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Habilidades (opcional)
            </label>
            <input type="text"
                   id="des_habilidades"
                   name="des_habilidades"
                   maxlength="255"
                   value="{{ old('des_habilidades', $trabalhador->des_habilidades ?? '') }}"
                   class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Ex: Tocar violão, tirar fotos, liderança...">
            @error('des_habilidades')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Primeira vez trabalhando --}}
        <div class="flex items-center">
            <input type="checkbox"
                   id="bol_primeira_vez"
                   name="bol_primeira_vez"
                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                   {{ old('bol_primeira_vez', $trabalhador->bol_primeira_vez ?? false) ? 'checked' : '' }}>
            <label for="bol_primeira_vez" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                É a primeira vez que trabalha em um evento?
            </label>
        </div>

        {{-- Botões --}}
        <div class="flex items-center gap-4">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:outline-none"
                aria-label="Salvar inscrição de trabalhador"
            >
                <x-heroicon-c-arrow-long-right class="w-5 h-5 mr-2" />
                Salvar
            </button>

            <a href="{{ route('trabalhadores.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-md focus:ring-2 focus:ring-gray-500 focus:outline-none text-gray-800"
               aria-label="Cancelar e voltar para a lista">
               <x-heroicon-o-x-mark class="w-5 h-5 mr-2" />
                Cancelar
            </a>
        </div>
    </form>
</x-layouts.app>
