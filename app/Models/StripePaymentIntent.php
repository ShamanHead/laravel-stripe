<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePaymentIntent extends Model
{
    use HasFactory;

    public $fillable = ['subscription_id', 'payment_method', 'amount', 'status', 'user_id'];

    public $table = 'payment_intent';

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'id', 'subscription_id');
    }
}
