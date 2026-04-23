<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Bienvenue, {{ Auth::user()->name }}. Gérez les films et les localisations depuis cette page.
            </p>

            {{-- Stats globales --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalFilms }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Films</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalLocalisations }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Localisations</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">+{{ $totalFilmUpvotes }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upvotes films</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-red-500">-{{ $totalFilmDownvotes }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Downvotes films</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <p class="text-2xl font-bold text-green-600">+{{ $totalLocVotes }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upvotes localisations</p>
                </div>
            </div>

            {{-- Actions --}}
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

            {{-- Top films --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Top 5 films par votes</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Film</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-600 uppercase">Upvotes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase">Downvotes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Score net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($topFilms as $film)
                            <tr>
                                <td class="px-6 py-3">
                                    <a href="{{ route('films.show', $film) }}"
                                       class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:underline">
                                        {{ $film->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-600">+{{ $film->upvotes_count }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-red-500">-{{ $film->downvotes_count }}</td>
                                <td class="px-6 py-3 text-sm font-semibold {{ $film->upvotes_count - $film->downvotes_count >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $film->upvotes_count - $film->downvotes_count >= 0 ? '+' : '' }}{{ $film->upvotes_count - $film->downvotes_count }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucun film.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Top localisations --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Top 5 localisations par upvotes</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Localisation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Film</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-green-600 uppercase">Upvotes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($topLocalisations as $localisation)
                            <tr>
                                <td class="px-6 py-3">
                                    <a href="{{ route('localisations.show', $localisation) }}"
                                       class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:underline">
                                        {{ $localisation->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $localisation->film->name ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-600">+{{ $localisation->upvotes_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Aucune localisation.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
