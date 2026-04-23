<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Abonnement CineMap
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Card tarif -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden mb-6">
                <div class="bg-indigo-600 px-6 py-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-lg font-bold">Abonnement Premium</h3>
                        <p class="text-indigo-200 text-sm mt-1">Accès à l'API JSON CineMap</p>
                    </div>
                    @if ($subscribed)
                        <span class="bg-green-400 text-white text-xs font-bold px-3 py-1 rounded-full">
                            Actif
                        </span>
                    @endif
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-baseline gap-1 mb-4">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">5 €</span>
                        <span class="text-gray-500 dark:text-gray-400">/ mois</span>
                    </div>
                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-bold">✓</span> Accès à l'API <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">GET /api/films/{film}/localisations</code>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-bold">✓</span> Authentification JWT
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-500 font-bold">✓</span> Résiliation à tout moment
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Formulaire paiement -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6 {{ $subscribed ? 'opacity-50 pointer-events-none select-none' : '' }}">

                @if ($subscribed)
                    <div class="flex items-center gap-2 mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-md">
                        <span class="text-green-600 font-bold text-lg">✓</span>
                        <p class="text-green-700 dark:text-green-400 text-sm font-medium">
                            Vous êtes déjà abonné. Le formulaire de paiement est désactivé.
                        </p>
                    </div>
                @endif

                <h4 class="text-gray-800 dark:text-gray-200 font-semibold mb-4">Informations de paiement</h4>

                <form id="payment-form" action="{{ route('subscription.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_method" id="payment_method">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Carte bancaire
                        </label>
                        <div id="card-element"
                             class="p-3 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-200">
                        </div>
                        <p id="card-errors" class="mt-2 text-sm text-red-600"></p>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Carte de test : <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">4242 4242 4242 4242</code> — date future, CVC quelconque.
                    </p>

                    <button type="submit" id="submit-btn" {{ $subscribed ? 'disabled' : '' }}
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ $subscribed ? 'Déjà abonné' : "S'abonner — 5 €/mois" }}
                    </button>
                </form>
            </div>

        </div>
    </div>

    @if (!$subscribed)
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const stripe = Stripe('{{ config('cashier.key') }}');
            const elements = stripe.elements();
            const cardElement = elements.create('card', {
                style: {
                    base: { fontSize: '16px', color: '#374151' }
                }
            });
            cardElement.mount('#card-element');

            const form = document.getElementById('payment-form');
            const submitBtn = document.getElementById('submit-btn');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Traitement en cours...';

                const { setupIntent, error } = await stripe.confirmCardSetup(
                    '{{ $intent->client_secret }}',
                    { payment_method: { card: cardElement } }
                );

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    submitBtn.disabled = false;
                    submitBtn.textContent = "S'abonner — 5 €/mois";
                } else {
                    document.getElementById('payment_method').value = setupIntent.payment_method;
                    form.submit();
                }
            });
        </script>
    @endif
</x-app-layout>
