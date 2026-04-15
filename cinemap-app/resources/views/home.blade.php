<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Accueil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @auth
                        <h3 class="text-lg font-semibold mb-2">Bienvenue, {{ Auth::user()->name }} !</h3>
                        <p class="text-gray-600 dark:text-gray-400">Vous êtes connecté à Cinemap.</p>
                    @else
                        <h3 class="text-lg font-semibold mb-2">Bienvenue sur Cinemap</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Connectez-vous ou créez un compte pour accéder à l'application.</p>
                        <div class="flex gap-4">
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md hover:bg-gray-700 dark:hover:bg-gray-300">
                                Se connecter
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 border border-gray-800 dark:border-gray-200 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                S'inscrire
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
