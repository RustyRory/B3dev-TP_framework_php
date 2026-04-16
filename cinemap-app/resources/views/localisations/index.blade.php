<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Localisations
            </h2>
            <a href="{{ route('localisations.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                Ajouter une localisation
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ville</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pays</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Film</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ajouté par</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($localisations as $localisation)
                            <tr>
                                <td class="px-6 py-4 text-gray-900 dark:text-gray-100 font-medium">
                                    <a href="{{ route('localisations.show', $localisation) }}" class="hover:underline">
                                        {{ $localisation->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $localisation->city }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $localisation->country }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if ($localisation->film)
                                        <a href="{{ route('films.show', $localisation->film) }}"
                                           class="text-blue-600 hover:underline dark:text-blue-400">
                                            {{ $localisation->film->name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $localisation->user->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('localisations.edit', $localisation) }}"
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Modifier</a>

                                    <form action="{{ route('localisations.destroy', $localisation) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer cette localisation ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Aucune localisation pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-6 py-4">
                    {{ $localisations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
