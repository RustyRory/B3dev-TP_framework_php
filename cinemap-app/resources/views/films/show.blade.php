<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $film->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Fiche film --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex gap-6">
                @if ($film->poster_url)
                    <div class="flex-shrink-0">
                        <img src="{{ $film->poster_url }}" alt="Affiche de {{ $film->name }}"
                             class="w-48 rounded shadow" onerror="this.style.display='none'" />
                    </div>
                @endif

                <div class="flex-1">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Réalisateur</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->producer }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Année de sortie</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->release_year }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Durée</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->time }} min</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Genres</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->genres }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Acteurs</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->actors }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Synopsis</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $film->synopsis }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trailer</dt>
                            <dd>
                                <a href="{{ $film->trailer_url }}" target="_blank"
                                   class="text-blue-600 hover:underline dark:text-blue-400">
                                    Voir le trailer
                                </a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Boutons de vote film --}}
            <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 flex items-center gap-4">
                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Voter :</span>
                @auth
                    <form action="{{ route('films.vote', $film) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_upvote" value="1">
                        <button type="submit"
                                class="{{ $userVote?->is_upvote === true ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} px-4 py-2 rounded-lg font-medium hover:opacity-80 transition">
                            +{{ $film->upvotes_count }}
                        </button>
                    </form>
                    <form action="{{ route('films.vote', $film) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_upvote" value="0">
                        <button type="submit"
                                class="{{ $userVote?->is_upvote === false ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} px-4 py-2 rounded-lg font-medium hover:opacity-80 transition">
                            -{{ $film->downvotes_count }}
                        </button>
                    </form>
                @else
                    <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                        +{{ $film->upvotes_count }}
                    </span>
                    <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium">
                        -{{ $film->downvotes_count }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Connectez-vous</a> pour voter.
                    </span>
                @endauth
            </div>

            {{-- Localisations --}}
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Lieux de tournage</h3>
                    @auth
                        <a href="{{ route('localisations.create', ['film_id' => $film->id]) }}"
                           class="text-sm border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 px-3 py-1.5 rounded-md hover:bg-blue-50 dark:hover:bg-gray-700">
                            + Ajouter une localisation
                        </a>
                    @endauth
                </div>

                @forelse ($film->localisations as $localisation)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 mb-2">
                        @if ($localisation->photo_url)
                            <img src="{{ $localisation->photo_url }}" alt="{{ $localisation->name }}"
                                 class="w-12 h-12 object-cover rounded flex-shrink-0"
                                 onerror="this.style.display='none'" />
                        @else
                            <div class="w-12 h-12 rounded bg-gray-200 dark:bg-gray-600 flex items-center justify-center flex-shrink-0 text-lg">
                                📍
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <a href="{{ route('localisations.show', $localisation) }}"
                               class="font-medium text-gray-900 dark:text-gray-100 hover:underline">
                                {{ $localisation->name }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $localisation->city }}, {{ $localisation->country }}
                            </p>
                        </div>

                        {{-- Vote + actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0">
                            {{-- Upvote --}}
                            @auth
                                @php $locVoted = $localisationVotes->has($localisation->id); @endphp
                                <form action="{{ route('localisations.vote', $localisation) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="{{ $locVoted ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }} text-xs px-2 py-1 rounded font-medium hover:opacity-80 transition">
                                        +{{ $localisation->upvotes_count }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded font-medium">
                                    +{{ $localisation->upvotes_count }}
                                </span>
                            @endauth

                            {{-- Modifier / Supprimer (propriétaire ou admin) --}}
                            @auth
                                @if (auth()->id() === $localisation->user_id || auth()->user()->is_admin)
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                    <a href="{{ route('localisations.edit', $localisation) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                        Modifier
                                    </a>
                                    <form action="{{ route('localisations.destroy', $localisation) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette localisation ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-900 dark:text-red-400">
                                            Supprimer
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Aucune localisation pour ce film.</p>
                @endforelse
            </div>

            <div class="mt-4">
                <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-400 hover:underline">
                    &larr; Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
