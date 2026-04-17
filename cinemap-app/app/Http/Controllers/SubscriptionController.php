<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    //

    public function index()
    {
        $user = auth()->user();

        return view('subscription.index', [
            'intent' => $user->subscribed('default') ? null : $user->createSetupIntent(),
            'subscribed' => $user->subscribed('default'),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->subscribed('default')) {
            return redirect()->route('subscription.index')
                ->with('error', 'Vous êtes déjà abonné.');
        }

        $request->validate(['payment_method' => 'required']);

        $user->createOrGetStripeCustomer();
        $user->addPaymentMethod($request->payment_method);
        $user->newSubscription('default', env('STRIPE_PRICE_ID'))
            ->create($request->payment_method);

        return redirect('/home')->with('success', 'Abonnement activé !');
    }
}
