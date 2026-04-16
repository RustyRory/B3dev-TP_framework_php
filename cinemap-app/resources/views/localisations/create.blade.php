<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Ajouter une localisation
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('localisations.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="film_id" value="Film associé" />
                        <select id="film_id" name="film_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                required>
                            <option value="">-- Choisir un film --</option>
                            @foreach ($films as $id => $name)
                                <option value="{{ $id }}" {{ old('film_id', request('film_id')) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('film_id')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="name" value="Nom du lieu" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      value="{{ old('name') }}" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <x-input-label for="city" value="Ville" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full"
                                          value="{{ old('city') }}" required />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="country" value="Pays" />
                            <x-text-input id="country" name="country" type="text" class="mt-1 block w-full"
                                          value="{{ old('country') }}" required />
                            <x-input-error :messages="$errors->get('country')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="4"
                                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                  required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="photo_url" value="URL de la photo (optionnel)" />
                        <x-text-input id="photo_url" name="photo_url" type="url" class="mt-1 block w-full"
                                      value="{{ old('photo_url') }}" />
                        <x-input-error :messages="$errors->get('photo_url')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Enregistrer</x-primary-button>
                        <a href="{{ request('film_id') ? route('films.show', request('film_id')) : route('home') }}"
                           class="text-gray-600 dark:text-gray-400 hover:underline">
                            Annuler
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
