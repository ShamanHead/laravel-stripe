<?php

namespace App\Jobs;

use App\Models\StripePaymentIntent;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RenewSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Stripe\Stripe::setApiKey(config('stripe.secret'));

        $dateTo = Carbon::now();
        $dateTo->subDays(30);

        // $subscriptionsQuery = Subscription::where(['is_active' => 1, 'last_intent' => 0, 'due_to' => $dateTo]);
        $subscriptionsQuery = Subscription::query();
        $count = $subscriptionsQuery->count();
        $pointer = 0;

        for ($page = 1; $page < ($count / 25) + 1; $page++) {
            $pool = $subscriptionsQuery
                ->with(['user', 'paymentIntent' => function ($query) {
                    $query->where('status', 'succeeded');
                    $query->orderBy('id', 'DESC');
                }])
                ->skip(($page - 1) * 25)
                ->limit(25)->get();

            foreach ($pool as $sub) {
                $paymentMethod = $sub->paymentIntent[0]->payment_method;

                $intent = \Stripe\PaymentIntent::create([
                    'amount' => 2000,
                    'currency' => 'usd',
                    'customer' => $sub->user->stripe_customer_id,
                    'payment_method' => $paymentMethod,
                    'confirm' => true,
                    'off_session' => true,
                ]);
                if ($intent->status === 'succeeded') {
                    $intentDb = new StripePaymentIntent(
                        [
                            'payment_method' => $paymentMethod,
                            'user_id' => $sub->user->id,
                            'amount' => $intent->amount,
                            'status' => $intent->status,
                        ]
                    );
                    $dateTo = Carbon::now();
                    $dateTo->addDays(30);
                    $sub->update(['due_to' => $dateTo]);
                } else {
                    $intentDb = new StripePaymentIntent(
                        [
                            'payment_method' => $paymentMethod,
                            'user_id' => $sub->user->id,
                            'amount' => $intent->amount,
                            'status_message' => $intent->last_payment_error->message,
                            'status' => $intent->status,
                        ]
                    );
                    $dateTo = Carbon::now();
                    $dateTo->addDays(2);
                    $sub->update(['due_to' => $dateTo, 'last_intent' => 1]);
                }
                $intent->save();
            }
        }
    }
}
