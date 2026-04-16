<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Modifier — {{ $film->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('films.update', $film) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <x-input-label for="name" value="Titre" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      value="{{ old('name', $film->name) }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="producer" value="Réalisateur" />
                        <x-text-input id="producer" name="producer" type="text" class="mt-1 block w-full"
                                      value="{{ old('producer', $film->producer) }}" required />
                        <x-input-error :messages="$errors->get('producer')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-input-label for="release_year" value="Année de sortie" />
                            <x-text-input id="release_year" name="release_year" type="number" class="mt-1 block w-full"
                                          value="{{ old('release_year', $film->release_year) }}" min="1888" :max="date('Y')" required />
                            <x-input-error :messages="$errors->get('release_year')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="time" value="Durée (minutes)" />
                            <x-text-input id="time" name="time" type="number" class="mt-1 block w-full"
                                          value="{{ old('time', $film->time) }}" min="1" required />
                            <x-input-error :messages="$errors->get('time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="genres" value="Genres (séparés par des virgules)" />
                        <x-text-input id="genres" name="genres" type="text" class="mt-1 block w-full"
                                      value="{{ old('genres', $film->genres) }}" required />
                        <x-input-error :messages="$errors->get('genres')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="actors" value="Acteurs (séparés par des virgules)" />
                        <x-text-input id="actors" name="actors" type="text" class="mt-1 block w-full"
                                      value="{{ old('actors', $film->actors) }}" required />
                        <x-input-error :messages="$errors->get('actors')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="synopsis" value="Synopsis" />
                        <textarea id="synopsis" name="synopsis" rows="4"
                                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                  required>{{ old('synopsis', $film->synopsis) }}</textarea>
                        <x-input-error :messages="$errors->get('synopsis')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="poster_url" value="URL de l'affiche" />
                        <x-text-input id="poster_url" name="poster_url" type="url" class="mt-1 block w-full"
                                      value="{{ old('poster_url', $film->poster_url) }}" required />
                        <x-input-error :messages="$errors->get('poster_url')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="trailer_url" value="URL du trailer" />
                        <x-text-input id="trailer_url" name="trailer_url" type="url" class="mt-1 block w-full"
                                      value="{{ old('trailer_url', $film->trailer_url) }}" required />
                        <x-input-error :messages="$errors->get('trailer_url')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Enregistrer les modifications</x-primary-button>
                        <a href="{{ route('films.show', $film) }}" class="text-gray-600 dark:text-gray-400 hover:underline">
                            Annuler
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
