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
                            {{-- Titre + votes film --}}
                            <div class="flex items-start justify-between gap-4 mb-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $film->name }}
                                </h3>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    @auth
                                        @php $filmVote = $filmVotes->get($film->id); @endphp
                                        <form action="{{ route('films.vote', $film) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="is_upvote" value="1">
                                            <button type="submit"
                                                    class="{{ $filmVote?->is_upvote === true ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} text-sm px-3 py-1 rounded-l-md font-medium hover:opacity-80 transition border-r border-white/20">
                                                +{{ $film->upvotes_count }}
                                            </button>
                                        </form>
                                        <form action="{{ route('films.vote', $film) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="is_upvote" value="0">
                                            <button type="submit"
                                                    class="{{ $filmVote?->is_upvote === false ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} text-sm px-3 py-1 rounded-r-md font-medium hover:opacity-80 transition">
                                                -{{ $film->downvotes_count }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-l-md font-medium">
                                            +{{ $film->upvotes_count }}
                                        </span>
                                        <span class="text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded-r-md font-medium">
                                            -{{ $film->downvotes_count }}
                                        </span>
                                    @endauth
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                {{ $film->producer }} &mdash; {{ $film->release_year }} &mdash; {{ $film->time }} min
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                {{ Str::limit($film->synopsis, 200) }}
                            </p>
                            <span class="inline-block text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded">
                                {{ $film->genres }}
                            </span>

                            <div class="mt-4 flex flex-wrap gap-3 items-center">
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
                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                                        @if ($localisation->photo_url)
                                            <img src="{{ $localisation->photo_url }}" alt="{{ $localisation->name }}"
                                                 class="w-10 h-10 object-cover rounded flex-shrink-0"
                                                 onerror="this.style.display='none'" />
                                        @else
                                            <div class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0 text-base">
                                                📍
                                            </div>
                                        @endif
                                        <a href="{{ route('localisations.show', $localisation) }}" class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate hover:underline">
                                                {{ $localisation->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $localisation->city }}, {{ $localisation->country }}
                                            </p>
                                        </a>
                                        {{-- Upvote localisation --}}
                                        @auth
                                            @php $locVoted = $localisationVotes->has($localisation->id); @endphp
                                            <form action="{{ route('localisations.vote', $localisation) }}" method="POST" class="flex-shrink-0">
                                                @csrf
                                                <button type="submit"
                                                        class="{{ $locVoted ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} text-xs px-2 py-1 rounded font-medium hover:opacity-80 transition">
                                                    +{{ $localisation->upvotes_count }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="flex-shrink-0 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded font-medium">
                                                +{{ $localisation->upvotes_count }}
                                            </span>
                                        @endauth
                                    </div>
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
