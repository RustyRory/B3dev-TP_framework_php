<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Films &amp; Lieux de tournage
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @forelse ($films as $film)
                <div class="mb-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                    {{-- Card film --}}
                    <div class="flex gap-6 p-6">
                        @if ($film->poster_url)
                            <div class="flex-shrink-0">
                                <img src="{{ $film->poster_url }}" alt="Affiche de {{ $film->name }}"
                                     class="w-32 rounded shadow object-cover"
                                     onerror="this.style.display='none'" />
                            </div>
                        @endif

                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {{ $film->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                {{ $film->producer }} &mdash; {{ $film->release_year }} &mdash; {{ $film->time }} min
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                {{ Str::limit($film->synopsis, 200) }}
                            </p>
                            <span class="inline-block text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                                {{ $film->genres }}
                            </span>

                            <div class="mt-4 flex gap-3">
                                <a href="{{ route('films.show', $film) }}"
                                   class="text-sm bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 px-4 py-2 rounded-md hover:bg-gray-700 dark:hover:bg-gray-300">
                                    Plus d'infos
                                </a>
                                @auth
                                    <a href="{{ route('localisations.create', ['film_id' => $film->id]) }}"
                                       class="text-sm border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 px-4 py-2 rounded-md hover:bg-blue-50 dark:hover:bg-gray-700">
                                        + Ajouter une localisation
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- Localisations --}}
                    @if ($film->localisations->isNotEmpty())
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                Lieux de tournage
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($film->localisations as $localisation)
                                    <a href="{{ route('localisations.show', $localisation) }}"
                                       class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                                        @if ($localisation->photo_url)
                                            <img src="{{ $localisation->photo_url }}" alt="{{ $localisation->name }}"
                                                 class="w-12 h-12 object-cover rounded flex-shrink-0"
                                                 onerror="this.style.display='none'" />
                                        @else
                                            <div class="w-12 h-12 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0 text-lg">
                                                📍
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate">
                                                {{ $localisation->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $localisation->city }}, {{ $localisation->country }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    Aucun film pour le moment.
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
