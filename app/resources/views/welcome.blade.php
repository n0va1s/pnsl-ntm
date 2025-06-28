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
            <div class="md:w-1/2 flex justify-center md:justify-end h-72 md:h-auto">
                <img src="https://www.vaticannews.va/content/dam/vaticannews/multimedia/2023/07/28/PORTUGAL-JMJ.jpg/_jcr_content/renditions/cq5dam.thumbnail.cropped.1500.844.jpeg"
                    alt="Jovem na Jornada Mundial da Juventude"
                    class="rounded-xl shadow-lg w-full h-72 md:h-full object-cover border border-gray-200 dark:border-gray-700">
            </div>
        </section>

        <!-- Seção: Movimentos -->
        <section class="text-center space-y-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-blue-400">
                Escolha o Movimento
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                <!-- Card VEM -->
                <div
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm">
                    <div>
                        <h3 class="text-xl font-bold text-blue-600 dark:text-blue-400">VEM</h3>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Encontro de Adolescentes com Cristo</p>
                    </div>
                    <a href="{{ route('fichas-vem.create') }}"
                        class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md transition">
                        Cadastrar Ficha VEM
                    </a>
                </div>

                <!-- Card Segue-Me -->
                <div
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm">
                    <div>
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
                    class="border border-gray-300 dark:border-gray-700 rounded-xl p-6 flex flex-col justify-between shadow-sm">
                    <div>
                        <h3 class="text-xl font-bold text-green-600 dark:text-green-400">ECC</h3>
                        <p class="text-gray-600 dark:text-gray-300 mt-2">Encontro de Casais com Cristo</p>
                    </div>
                    <a href="{{ route('fichas-ecc.create') }}"
                        class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-md transition">
                        Cadastrar Ficha ECC
                    </a>
                </div>
            </div>
        </section>
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

            <a href="https://www.instagram.com/vempnsl" target="_blank" class="flex items-center gap-2 hover:underline">
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
