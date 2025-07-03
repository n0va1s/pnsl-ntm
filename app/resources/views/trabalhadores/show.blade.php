<x-layouts.app :title="'Detalhes do Trabalhador'">
    <section class="p-6 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Detalhes do Trabalhador</h1>

        <div class="mb-4">
            <strong>Nome Completo:</strong> {{ $trabalhador->nom_pessoa }}
        </div>

        <div class="mb-4">
            <strong>Apelido:</strong> {{ $trabalhador->nom_apelido ?? '—' }}
        </div>

        <div class="mb-4">
            <strong>Telefone:</strong> {{ $trabalhador->tel_pessoa }}
        </div>

        <div class="mb-4">
            <strong>Endereço:</strong> {{ $trabalhador->des_endereco ?? '—' }}
        </div>

        <div class="mb-4">
            <strong>Trabalha nas equipes:</strong>
            <ul class="list-disc pl-5">
                @foreach ($trabalhador->trabalhador as $trabalho)
                    <li>
                        Evento: {{ $trabalho->evento->des_evento ?? 'Sem evento' }}<br>
                        Equipe: {{ $trabalho->equipe->des_grupo ?? 'Sem equipe' }}<br>
                        Habilidades: {{ $trabalho->des_habilidades ?? '—' }}<br>
                        Primeira vez? {{ $trabalho->bol_primeira_vez ? 'Sim' : 'Não' }}
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="{{ route('trabalhadores.index') }}" class="mt-4 inline-block px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
            Voltar à lista
        </a>
    </section>
</x-layouts.app>
