<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            À propos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Présentation du projet --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-5xl">🎬</span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">CineMap</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Travail Pratique — Framework PHP</p>
                    </div>
                </div>

                <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                    CineMap est une application web développée dans le cadre d'un <strong>travail pratique de formation B3</strong>
                    à <strong>MyDigitalSchool Angers</strong>. Elle permet de recenser les lieux de tournage de films,
                    de les voter et d'en ajouter de nouveaux.
                </p>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    Le projet a pour objectif de maîtriser le framework <strong>Laravel</strong> : routing, Eloquent ORM,
                    middleware, authentification, API REST, CI/CD et déploiement sur VPS.
                </p>
            </div>

            {{-- Stack technique --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Stack technique</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ([
                        ['Laravel 12', 'Framework PHP'],
                        ['PHP 8.3', 'Langage'],
                        ['MySQL', 'Base de données'],
                        ['Tailwind CSS', 'Styles'],
                        ['Alpine.js', 'Interactivité'],
                        ['Laravel Breeze', 'Authentification'],
                        ['Discord OAuth', 'Connexion sociale'],
                        ['GitHub Actions', 'CI/CD'],
                        ['VPS Ubuntu', 'Hébergement'],
                    ] as [$name, $role])
                        <div class="flex flex-col px-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Auteur --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Auteur</h2>
                <div class="flex items-center gap-4">
                    <img
                        src="https://github.com/RustyRory.png"
                        alt="RustyRory"
                        class="w-16 h-16 rounded-full border-2 border-gray-200 dark:border-gray-600"
                        onerror="this.src='https://ui-avatars.com/api/?name=RustyRory&background=374151&color=fff'"
                    />
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">RustyRory</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Étudiant B3 Dev — MyDigitalSchool Angers</p>
                        <a
                            href="https://github.com/RustyRory"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 mt-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                            </svg>
                            github.com/RustyRory
                        </a>
                    </div>
                </div>
            </div>

            {{-- Liens --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Liens</h2>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="https://github.com/RustyRory/B3dev-TP_framework_php"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 px-4 py-2 rounded-md hover:bg-gray-700 dark:hover:bg-gray-300 transition"
                    >
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                        </svg>
                        Code source
                    </a>
                    <a
                        href="http://78.138.58.95/cinemap/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                        </svg>
                        CineMap en ligne
                    </a>
                    <a
                        href="http://78.138.58.95/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        Autres projets VPS
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
