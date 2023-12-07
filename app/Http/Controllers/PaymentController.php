<?php

namespace App\Http\Controllers;

use App\Models\StripePaymentIntent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        try {
            Stripe::setApiKey(config('stripe.secret'));
            $header = $request->server('HTTP_STRIPE_SIGNATURE');
            $payload = $request->getContent();
            $webhookKey = config('stripe.webhook');

            $event = \Stripe\Webhook::constructEvent(
                $payload, $header, $webhookKey
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($event->data->object->id);

                    $userId = User::firstWhere('stripe_customer_id', $paymentIntent->customer)->id;

                    $subscription = Subscription::firstWhere(['user_id' => $userId, 'is_active' => 1, 'last_intent' => 0]);

                    if ($subscription) {
                        $intent = StripePaymentIntent::create([
                            'payment_method' => $paymentIntent->payment_method,
                            'user_id' => $userId,
                            'amount' => $paymentIntent->amount,
                            'status' => $paymentIntent->status,
                        ]);
                    } else {
                        $user = User::find($userId);
                        $dueTo = Carbon::now();
                        $dueTo->addDays(30);
                        $subscription = Subscription::create([
                            'user_id' => $userId,
                            'due_to' => $dueTo,
                            'is_active' => true,
                        ]);

                        $intent = StripePaymentIntent::create([
                            'payment_method' => $paymentIntent->payment_method,
                            'user_id' => $userId,
                            'amount' => $paymentIntent->amount,
                            'status' => $paymentIntent->status,
                        ]);
                    }

                    $subscription->paymentIntent()->attach($intent);
                    break;
                case 'payment_intent.payment_failed':
                    $paymentIntent = \Stripe\PaymentIntent::retrieve($event->data->object->id);

                    $userId = $paymentIntent->customer;

                    $intent = StripePaymentIntent::create([
                        'payment_method' => $paymentIntent->payment_method,
                        'user_id' => $userId,
                        'amount' => $paymentIntent->amount,
                        'status_message' => $paymentIntent->last_payment_error->message,
                        'status' => $paymentIntent->status,
                    ]);
                    break;
                default:
                    Log::info('[Payment] There is '.$event->type.' event occured');

                    return response('', 501);
                    break;
            }
        } catch (\Exception $e) {
            report($e);

            return response('', 500);
        }
    }
}
