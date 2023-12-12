<?php

namespace App\Http\Controllers;

use App\Jobs\RenewSubscription;
use App\Models\StripePaymentIntent;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function form()
    {
        $user = Auth::user();
        $userId = $user->id;
        $paymentIntents = StripePaymentIntent::where('user_id', $userId)->paginate(10);
        $clientSecret = $subscriptionExpiry = null;

        $subscription = Subscription::where(['user_id' => $userId, 'is_active' => 1, 'last_intent' => 0])
            ->orWhere(['user_id' => $userId, 'last_intent' => 1])->first();

        $subscriptionActive = $subscriptionDeclined = false;

        if ($subscription && $subscription->last_intent === 1) {
            $subscriptionDeclined = true;
        }

        if ($subscription && ($subscription->is_active || $subscriptionDeclined)) {
            $subscriptionExpiry = Carbon::parse($subscription->due_to)->toDateString();
            $subscriptionActive = true;
        } else {
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $intent = $stripe->paymentIntents->create([
                'amount' => 2000,
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'setup_future_usage' => 'off_session',
            ]);

            $clientSecret = $intent->client_secret;

            $subscriptionActive = false;
        }

        return view('subscription', compact('subscriptionExpiry', 'paymentIntents', 'clientSecret', 'subscriptionActive', 'subscriptionDeclined'));
    }

    public function success(string $id) {
        return view('success', compact('id'));
    }

    public function cancel()
    {
        Subscription::where(['user_id' => Auth::user()->id, 'is_active' => 1, 'last_intent' => 0])->update(['last_intent' => 1]);
    }
}
