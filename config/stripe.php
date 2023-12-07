<?php

return [
    'secret' => env('STRIPE_SECRET_KEY', null),
    'public' => env('STRIPE_PUBLIC_KEY', null),
    'webhook' => env('STRIPE_WEBHOOK_KEY', null),
];
