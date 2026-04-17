<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    //

    public function index()
    {
        return view('subscription.index', [
            'intent' => auth()->user()->createSetupIntent(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['payment_method' => 'required']);

        $user = auth()->user();

        $user->createOrGetStripeCustomer();

        $user->addPaymentMethod($request->payment_method);

        $user->newSubscription('default', env('STRIPE_PRICE_ID'))
            ->create($request->payment_method);

        return redirect('/home')->with('success', 'Abonnement activé !');
    }
}
