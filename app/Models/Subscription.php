<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subscription extends Model
{
    use HasFactory;

    public $fillable = ['user_id', 'due_to', 'is_active', 'last_intent'];

    public $table = 'subscriptions';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function paymentIntent(): BelongsToMany
    {
        return $this->belongsToMany(StripePaymentIntent::class, 'subscription_payment_intent', 'subscription_id', 'payment_intent_id');
    }
}
