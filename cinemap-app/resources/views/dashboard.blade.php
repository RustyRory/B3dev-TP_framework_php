<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Bienvenue, {{ Auth::user()->name }}. Gérez les films et les localisations depuis cette page.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- Card Films --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Films</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Ajouter, modifier ou supprimer des films.
                        </p>
                    </div>
                    <div class="flex gap-3 mt-auto">
                        <a href="{{ route('films.index') }}"
                           class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-700 dark:hover:bg-gray-300">
                            Gérer les films
                        </a>
                        <a href="{{ route('films.create') }}"
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            + Nouveau film
                        </a>
                    </div>
                </div>

                {{-- Card Localisations --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Localisations</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Consulter et modérer toutes les localisations.
                        </p>
                    </div>
                    <div class="flex gap-3 mt-auto">
                        <a href="{{ route('localisations.index') }}"
                           class="px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 text-sm font-semibold rounded-md hover:bg-gray-700 dark:hover:bg-gray-300">
                            Gérer les localisations
                        </a>
                        <a href="{{ route('localisations.create') }}"
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                            + Nouvelle localisation
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
