<?php

namespace App\Http\Controllers;

use App\Models\StripePaymentIntent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        try {
            DB::beginTransaction();

            Stripe::setApiKey(config('stripe.secret'));
            $webhookKey = config('stripe.webhook');

            try {
                $event = \Stripe\Event::constructFrom(
                    $request->all()
                );
            } catch (\UnexpectedValueException $e) {
                return response(400);
            }

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($event->data->object->id);

                    $userId = User::firstWhere('stripe_customer_id', $paymentIntent->customer)->id;

                    $subscription = Subscription::firstWhere(['user_id' => $userId, 'is_active' => 1, 'last_intent' => 0]);

                    if (!$subscription) {
                        $user = User::find($userId);
                        $dueTo = Carbon::now();
                        $dueTo->addDays(30);
                        $subscription = Subscription::create([
                            'user_id' => $userId,
                            'due_to' => $dueTo,
                            'is_active' => true,
                        ]);
                    }

                    $intent = StripePaymentIntent::firstOrCreate(
                        ['stripe_id' => $paymentIntent->id],
                        [
                            'stripe_id' => $paymentIntent->id,
                            'payment_method' => $paymentIntent->payment_method,
                            'user_id' => $userId,
                            'amount' => $paymentIntent->amount,
                            'status' => $paymentIntent->status,
                        ]
                    );

                    $subscription->paymentIntent()->attach($intent);

                    DB::commit();
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($event->data->object->id);

                    $userId = $paymentIntent->customer;

                    $intent = StripePaymentIntent::firstOrCreate([
                        'stripe_id' => $paymentIntent->id,
                        'payment_method' => $paymentIntent->payment_method,
                        'user_id' => $userId,
                        'amount' => $paymentIntent->amount,
                        'status_message' => $paymentIntent->last_payment_error->message,
                        'status' => $paymentIntent->status,
                    ]);
                    break;
                default:
                    Log::info('[Payment] There is '.$event->type.' event occured');

                    return response('Not implemented.', 501);
                    break;
            }
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            return response('', 500);
        }
    }

    public function check(string $id)
    {
        $intent = StripePaymentIntent::firstWhere('stripe_id', $id);

        if (! $intent) {
            return response('Not found.', 404);
        } elseif ($intent->status === 'succeeded') {
            return response('OK', 200);
        } else {
            return response('Payment failed', 500);
        }
    }
}
