<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
            <div class="page">
                <section class="panel stack-sm">
                    <div>
                        <h3 class="section-title">Administration du site</h3>
                        <p class="section-text">Accedez a la gestion des films et des localisations depuis cette page.</p>
                    </div>

                    <div class="action-group">
                        <a class="button" href="{{ route('films.index') }}">Gerer les films</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
