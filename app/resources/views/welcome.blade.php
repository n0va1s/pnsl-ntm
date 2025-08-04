<!DOCTYPE html>
<html lang="pt-BR" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao NTM</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex flex-col">

    <!-- Conteúdo principal -->
    <main class="flex-1 px-6 py-12 max-w-7xl mx-auto space-y-20">
        <header class="bg-white dark:bg-gray-900 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="flex text-lg font-semibold text-gray-800 dark:text-gray-100 items-center">
                   <x-app-logo />
                </h1>
                <div class="space-x-4">
                    <a href="/dashboard"
                        class="text-sm font-medium px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white transition">
                        Área Restrita </a>
                    <a href="/register"
                        class="text-sm font-medium px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Cadastre-se
                    </a>
                </div>
            </div>
        </header>

        <!-- Seção: Sistema de Gestão -->
        <section
            class="flex flex-col md:flex-row items-center justify-center text-center md:text-left space-y-6 md:space-y-0 md:space-x-10">
            <div class="md:w-1/2 space-y-4">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-400">
                    Sistema de Gestão de Movimentos Paroquiais
                </h2>
                <p class="max-w-2xl mx-auto md:mx-0 text-gray-600 dark:text-gray-300">
                    Bem-vindo! Este sistema foi criado para facilitar o cadastro e a organização dos encontros
                    promovidos
                    pela Paraóquia Nossa Senhora do Lago, como o VEM, ECC e Segue-Me.
                    <br><br>
                    A sigla NTM significa "Não tenhais medo!", frase várias vezes repetida pelo Papa João Paulo II e
                    Francisco para encorajar os jovens de todo o mundo para que não tenham medo de buscar a Cristo.
                </p>
            </div>
            <div>
            <div class="relative w-full max-w-2xl mx-auto overflow-hidden rounded-2xl" id="carousel">
                <div id="carouselSlides" class="flex transition-transform duration-500">
                    <img src="https://i.imgur.com/yXiQHE9.jpeg" class="w-full h-64 md:h-100 object-cover flex-shrink-0 carousel-img " alt="Imagem 1">
                    <img src="https://i.imgur.com/FdbulA4.jpeg" class="w-full h-64 md:h-100 object-cover flex-shrink-0 carousel-img " alt="Imagem 2">
                    <img src="https://i.imgur.com/piduEFx.jpeg" class="w-full h-64 md:h-100 object-cover flex-shrink-0 carousel-img " alt="Imagem 3">
                </div>

                <!-- Dots navigation -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                    <button onclick="showSlide(0)" class="w-3 h-3 rounded-full transition-all duration-300 cursor-pointer hover:bg-blue-600" id="dot-0"></button>
                    <button onclick="showSlide(1)" class="w-3 h-3 rounded-full transition-all duration-300 cursor-pointer hover:bg-blue-600" id="dot-1"></button>
                    <button onclick="showSlide(2)" class="w-3 h-3 rounded-full transition-all duration-300 cursor-pointer hover:bg-blue-600" id="dot-2"></button>
                </div>

                <button onclick="prevSlide()"
                    class="absolute top-1/2 left-0 transform -translate-y-1/2 bg-opacity-50 text-white p-3 text-2xl z-10 hover: transition cursor-pointer"
                    onmouseover="this.querySelector('svg').style.transform='scale(1.4)'"
                    onmouseout="this.querySelector('svg').style.transform='scale(1)'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button onclick="nextSlide()"
                    class="absolute top-1/2 right-0 transform -translate-y-1/2 bg-opacity-50 text-white p-3 text-2xl z-10 hover:bg-opacity-70 transition cursor-pointer"
                    onmouseover="this.querySelector('svg').style.transform='scale(1.4)'"
                    onmouseout="this.querySelector('svg').style.transform='scale(1)'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <script>
                let index = 0;
                const slides = document.getElementById('carouselSlides');
                const totalSlides = slides.querySelectorAll('.carousel-img').length;
                const dots = Array.from({length: totalSlides}, (_, i) => document.getElementById(`dot-${i}`));

                function updateDots() {
                    dots.forEach((dot, i) => {
                        if (i === index) {
                            dot.classList.add('bg-blue-600');
                            dot.classList.remove('bg-gray-300');
                        } else {
                            dot.classList.add('bg-gray-300');
                            dot.classList.remove('bg-blue-600');
                        }
                    });
                }

                function showSlide(i) {
                    index = (i + totalSlides) % totalSlides;
                    slides.style.transform = `translateX(-${index * 100}%)`;
                    updateDots();
                }

                function nextSlide() {
                    showSlide(index + 1);
                }

                function prevSlide() {
                    showSlide(index - 1);
                }

                setInterval(() => {
                    nextSlide();
                }, 7000);

                showSlide(index);
            </script>


        </section>

        <!-- Seção: Movimentos -->
        <section class="text-center space-y-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-blue-400">
                Escolha o Movimento
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                <!-- Card VEM -->
                <div
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm dark:bg-gray-800">
                    <div>
                        <img
                        src="https://i.imgur.com/JmjVysp.png"
                        class="w-full h-64 md:h-30 object-cover flex-shrink-0 rounded-2xl"
                        alt="Imagem 1">
                        <h3 class="text-xl font-bold text-blue-600 dark:text-blue-400">VEM</h3>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Encontro de Adolescentes com Cristo</p>
                    </div>
                    <a href="{{ route('vem.create') }}"
                        class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
                        Cadastrar Ficha VEM
                    </a>
                </div>

                <!-- Card Segue-Me -->
                <div
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm dark:bg-gray-800">
                    <div>
                         <img
                        src="https://i.imgur.com/U3FfnPu.png"
                        class="w-full h-64 md:h-30 object-contain flex-shrink-0 rounded-2xl"
                        alt="Imagem 1">
                        <h3 class="text-xl font-bold text-orange-600 dark:text-orange-400">Segue-Me</h3>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Encontro de Jovens com Cristo</p>
                    </div>
                    <a href=""
                        class="mt-4 inline-block bg-orange-600 hover:bg-orange-700 text-white font-semibold px-4 py-2 rounded-md transition">
                        Cadastrar Ficha Segue-Me
                    </a>
                </div>

                <!-- Card ECC -->
                <div
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm dark:bg-gray-800">
                    <div>
                         <img
                        src="https://i.imgur.com/aaifcaH.png"
                        class="w-full h-64 md:h-30 object-contain flex-shrink-0 rounded-2xl"
                        alt="Imagem 1">
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">ECC</h3>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Encontro de Casais com Cristo</p>
                    </div>
                    <a href="{{ route('ecc.create') }}"
                        class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md transition">
                        Cadastrar Ficha ECC
                    </a>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-7xl mx-auto my-16 px-4">
            <!-- FAQ VEM -->
            <section class="max-w-full bg-blue-50 dark:bg-blue-900 rounded-xl p-6 shadow">
                <h4 class="font-bold text-blue-600 mb-8 dark:text-blue-400">Perguntas Frequentes - VEM</h4>
                <div class="space-y-4">
                    <details class="group border border-blue-200 rounded-lg p-4 bg-white dark:bg-blue-800">
                        <summary class="flex justify-between items-center cursor-pointer text-blue-700 font-medium dark:text-blue-400">
                            Quem pode participar do VEM?
                            <svg class="w-5 h-5 transform transition-transform group-open:rotate-180 text-blue-600"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="mt-2 text-gray-700 dark:text-gray-100">Jovens solteiros entre 12 e 15 anos que
                            desejam iniciar sua caminhada cristã.</p>
                    </details>
                    <details class="group border border-blue-200 rounded-lg p-4 bg-white dark:bg-blue-800">
                        <summary class="flex justify-between items-center cursor-pointer text-blue-700 font-medium dark:text-blue-400">
                            Quanto tempo acontece o VEM?
                            <svg class="w-5 h-5 transform transition-transform group-open:rotate-180 text-blue-600"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="mt-2 text-gray-700 dark:text-gray-100">O VEM é um movimento que acontece há mais de 30 anos.</p>
                    </details>
                </div>
            </section>
            <!-- FAQ Segue-Me -->
            <section class="max-w-full bg-orange-50 dark:bg-orange-900 rounded-xl p-6 shadow">
                <h4 class="font-bold text-orange-600 mb-8 dark:text-orange-400">Perguntas Frequentes - Segue-Me</h4>
                <div class="space-y-4">
                    <details class="group border border-orange-200 rounded-lg p-4 bg-white dark:bg-orange-800">
                        <summary class="flex justify-between items-center cursor-pointer text-orange-700 font-medium dark:text-orange-400">
                            Quem pode participar do Segue-Me?
                            <svg class="w-5 h-5 transform transition-transform group-open:rotate-180 text-orange-600"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p clas="mt-2 text-gray-700 dark:text-gray-100">Jovens solteiros entre 17 e 30 anos que
                            desejam aprofundar sua caminhada cristã.</p>
                    </details>
                </div>
            </section>

            <!-- FAQ Segue-Me -->
            <section class="max-w-full bg-green-50 dark:bg-green-900 rounded-xl p-6 shadow">
                <h4 class="font-bold text-green-600 mb-8 dark:text-green-200">Perguntas Frequentes - ECC</h4>
                <div class="space-y-4">
                    <details class="group border border-green-200 rounded-lg p-4 bg-white dark:bg-green-800">
                        <summary class="flex justify-between items-center cursor-pointer text-green-700 font-medium dark:text-green-600">
                            Quem pode participar do ECC?
                            <svg class="w-5 h-5 transform transition-transform group-open:rotate-180 text-green-600"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="mt-2 text-gray-700 dark:text-gray-100">Casais de todas as idades</p>
                    </details>
                </div>
            </section>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-8 max-w-7xl mx-auto my-16 px-4">
            <!-- Seção 60% -->
            <div class="md:col-span-3">
                <section class="max-w-3xl mx-auto mt-24 px-4">
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Próximos Eventos</h2>
                    {{-- Lista de eventos --}}
                    <ul class="space-y-4">
                        @forelse ($proximoseventos as $evento)
                            @php
                                $sigla = $evento->movimento?->des_sigla;
                                $badgeClasses = match ($sigla) {
                                    'VEM' => 'bg-blue-100 text-blue-700',
                                    'Segue-Me' => 'bg-orange-100 text-orange-700',
                                    'ECC' => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <li
                                class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                                <x-heroicon-o-calendar class="w-6 h-6 text-blue-500 mt-1" />
                                <div class="flex-1 flex flex-col w-full">
                                    <div class="flex justify-between items-start">
                                        <h3 class="text-base font-medium text-gray-900 dark:text-white">
                                            {{ $evento->des_evento }}
                                        </h3>

                                        <span
                                            class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClasses }}">
                                            {{ $sigla }}
                                        </span>
                                    </div>

                                    {{-- Informações adicionais --}}
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($evento->dat_inicio)->translatedFormat('d/m/Y') }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li class="text-gray-600 dark:text-gray-300">Nenhum evento encontrado.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
            <!-- Seção 40% -->
            <div class="md:col-span-2">
                <section class="max-w-3xl mx-auto mt-24 px-4">
                    @if (session('message'))
                        <div class="max-w-3xl mx-auto mt-6 px-4">
                            <div
                                class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 p-4 rounded-lg">
                                <p class="font-semibold">{{ session('message') }}</p>
                            </div>
                        </div>
                    @endif
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Entre em Contato</h2>

                    <form action="{{ route('home.contato') }}" method="POST"
                        class="space-y-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                        @csrf

                        <!-- Nome -->
                        <div>
                            <label for="nom_contato" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nome <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="text" name="nom_contato" id="nom_contato" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <!-- Email -->
                        <div>
                            <label for="eml_contato"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="eml_contato" id="eml_contato"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Telefone -->
                        <div>
                            <label for="tel_contato"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <input type="tel" name="tel_contato" id="tel_contato" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Movimento -->
                        <div>
                            <label for="idt_movimento"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Movimento <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <select name="idt_movimento" id="idt_movimento" required
                                class="w-full rounded-md border border-gray-300 dark:border-zinc-600 px-3 py-2 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-100">
                                <option value="">Selecione um movimento</option>
                                @foreach ($movimentos as $movimento)
                                    <option value="{{ $movimento->idt_movimento }}">{{ $movimento->des_sigla }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Mensagem -->
                        <div>
                            <label for="txt_mensagem"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem <span class="text-red-600 dark:text-red-400">*</span>
                            </label>
                            <textarea name="txt_mensagem" id="txt_mensagem" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Botão -->
                        <div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
                                Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="bg-gray-100 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-300 py-6">
        <div class="flex flex-col sm:flex-row justify-center items-center gap-6">

            <a href="https://www.instagram.com/nossasenhoradolago" target="_blank"
                class="flex items-center gap-2 hover:underline">
                <!-- Instagram Icon -->
                <svg class="w-6 h-6 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        d="M7.75 2A5.75 5.75 0 0 0 2 7.75v8.5A5.75 5.75 0 0 0 7.75 22h8.5A5.75 5.75 0 0 0 22 16.25v-8.5A5.75 5.75 0 0 0 16.25 2h-8.5ZM4.5 7.75a3.25 3.25 0 0 1 3.25-3.25h8.5a3.25 3.25 0 0 1 3.25 3.25v8.5a3.25 3.25 0 0 1-3.25 3.25h-8.5a3.25 3.25 0 0 1-3.25-3.25v-8.5Zm7.5 1.25a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 1.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM17.5 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" />
                </svg>
                Instagram da Paróquia
            </a>

            <a href="https://www.instagram.com/vempnsl" target="_blank"
                class="flex items-center gap-2 hover:underline">
                <svg class="w-6 h-6 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        d="M7.75 2A5.75 5.75 0 0 0 2 7.75v8.5A5.75 5.75 0 0 0 7.75 22h8.5A5.75 5.75 0 0 0 22 16.25v-8.5A5.75 5.75 0 0 0 16.25 2h-8.5ZM4.5 7.75a3.25 3.25 0 0 1 3.25-3.25h8.5a3.25 3.25 0 0 1 3.25 3.25v8.5a3.25 3.25 0 0 1-3.25 3.25h-8.5a3.25 3.25 0 0 1-3.25-3.25v-8.5Zm7.5 1.25a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 1.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM17.5 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" />
                </svg>
                Fale com o VEM
            </a>

            <a href="https://www.instagram.com/seguemepnsl" target="_blank"
                class="flex items-center gap-2 hover:underline">
                <svg class="w-6 h-6 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        d="M7.75 2A5.75 5.75 0 0 0 2 7.75v8.5A5.75 5.75 0 0 0 7.75 22h8.5A5.75 5.75 0 0 0 22 16.25v-8.5A5.75 5.75 0 0 0 16.25 2h-8.5ZM4.5 7.75a3.25 3.25 0 0 1 3.25-3.25h8.5a3.25 3.25 0 0 1 3.25 3.25v8.5a3.25 3.25 0 0 1-3.25 3.25h-8.5a3.25 3.25 0 0 1-3.25-3.25v-8.5Zm7.5 1.25a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 1.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM17.5 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" />
                </svg>
                Fale com o Segue-Me
            </a>

            <a href="https://www.facebook.com/lourdes.ecc/" target="_blank"
                class="flex items-center gap-2 hover:underline">
                <svg class="w-6 h-6 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2v-2.9h2v-2.2c0-2 1.2-3.1 3-3.1.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.2v1.9h2.2l-.4 2.9h-1.8v7A10 10 0 0 0 22 12Z" />
                </svg>
                Fale com o ECC
            </a>

        </div>


    </footer>

</body>

</html>
