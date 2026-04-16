<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $film->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('films.edit', $film) }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                    Modifier
                </a>
                <form action="{{ route('films.destroy', $film) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce film ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex gap-6">

                <div class="flex-shrink-0">
                    <img src="{{ $film->poster_url }}" alt="Affiche de {{ $film->name }}"
                         class="w-48 rounded shadow" onerror="this.style.display='none'" />
                </div>

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

            <div class="mt-4">
                <a href="{{ route('films.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline">
                    &larr; Retour à la liste
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
