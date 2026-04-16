<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $localisation->name }}
            </h2>
            @auth
                @if (auth()->id() === $localisation->user_id)
                    <div class="flex gap-2">
                        <a href="{{ route('localisations.edit', $localisation) }}"
                           class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded">
                            Modifier
                        </a>
                        <form action="{{ route('localisations.destroy', $localisation) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette localisation ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
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

                @if ($localisation->photo_url)
                    <div class="flex-shrink-0">
                        <img src="{{ $localisation->photo_url }}" alt="Photo de {{ $localisation->name }}"
                             class="w-48 rounded shadow object-cover" onerror="this.style.display='none'" />
                    </div>
                @endif

                <div class="flex-1">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ville</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $localisation->city }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pays</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $localisation->country }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Film associé</dt>
                            <dd class="text-gray-900 dark:text-gray-100">
                                <a href="{{ route('films.show', $localisation->film) }}"
                                   class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $localisation->film->name }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $localisation->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ajouté par</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $localisation->user->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Votes</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $localisation->upvotes_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('films.show', $localisation->film) }}"
                   class="text-gray-600 dark:text-gray-400 hover:underline">
                    &larr; Retour au film
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
