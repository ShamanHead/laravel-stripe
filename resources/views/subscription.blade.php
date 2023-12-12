<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(!$subscriptionActive && !$subscriptionDeclined)
                    <form id="payment-form" class="mt-4">
                        <div class="space-y-4">
                            <div class="mb-4">
                                <h2 class="text-2xl font-bold">Credit or debit card</h2>
                            </div>
                            <div id="card-element"
                                class="mt-1 px-4 py-2 block w-full border border-gray-300 rounded-md"></div>
                            <div id="card-errors" class="text-red-500"></div>
                        </div>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 mt-4 bg-blue-500 border border-transparent rounded-md font-semibold text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ ('Submit Payment') }}
                        </button>
                    </form>
                    @else
                    <div
                        class="relative bg-green-100 dark:bg-gray-800 border border-green-300 dark:border-gray-700 rounded-md p-4 mb-4">
                        <p class="text-green-600 dark:text-gray-200 font-semibold">Your subscription is active until {{
                            $subscriptionExpiry }}. @if(!$subscriptionDeclined) Automatic renewal is on @endif</p>
                        @if(!$subscriptionDeclined)
                        <button
                            class="absolute top-0 right-0 mt-3 mr-2 p-2 rounded-full bg-white dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none"
                            onclick="cancelSubscription()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                    @endif
                    <script src="https://js.stripe.com/v3/"></script>
                    <script>
                        @if (!$subscriptionActive)
                            var stripe = Stripe('{{ config("stripe.public") }}');
                        var elements = stripe.elements();

                        var cardElement = elements.create('card');
                        cardElement.mount('#card-element');

                        var form = document.getElementById('payment-form');
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            stripe.confirmCardPayment('{{ $clientSecret }}', {
                                payment_method: {
                                    card: cardElement
                                }
                            }).then(function (confirmResult) {
                                // debugger;
                                if (confirmResult.error) {
                                    var errorElement = document.getElementById('card-errors');
                                    errorElement.textContent = confirmResult.error.message;
                                } else {
                                    window.location = 'success/' + confirmResult.paymentIntent.id
                                }
                            });
                        });
                        @else
                        function cancelSubscription() {
                            // Send a request to the cancel endpoint
                            fetch('/subscription/cancel', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                            })
                                .then(response => {
                                    location.reload();
                                })
                                .catch(error => console.error(error));
                        }
                        @endif
                    </script>
                </div>
                @if(count($paymentIntents) > 0)
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold">Payment Intents</h2>
                    </div>
                    <div class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="py-2">ID</th>
                                    <th class="py-2">Amount</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Created At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($paymentIntents as $paymentIntent)
                                <tr>
                                    <td class="py-2 text-center">{{ $paymentIntent->id }}</td>
                                    <td class="py-2 text-center">{{ $paymentIntent->amount }}</td>
                                    <td class="py-2 text-center">{{ $paymentIntent->status }}</td>
                                    <td class="py-2 text-center">{{ $paymentIntent->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $paymentIntents->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
