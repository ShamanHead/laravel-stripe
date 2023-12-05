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
                    <form id="payment-form" class="mt-4">
                        <div class="space-y-4">
                            <label for="card-element" class="block font-medium text-gray-700">
                                {{ __('Credit or debit card') }}
                            </label>
                            <div id="card-element"
                                class="mt-1 px-4 py-2 block w-full border border-gray-300 rounded-md"></div>
                            <div id="card-errors" class="text-red-500"></div>
                        </div>

                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 mt-4 bg-blue-500 border border-transparent rounded-md font-semibold text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Submit Payment') }}
                        </button>
                    </form>
                    <script src="https://js.stripe.com/v3/"></script>
                    <script>
                        var stripe = Stripe('{{ config("stripe.public") }}');
                        var elements = stripe.elements();

                        var cardElement = elements.create('card');
                        cardElement.mount('#card-element');

                        var form = document.getElementById('payment-form');
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();

                            stripe.confirmCardPayment('{{ config("stripe.secret") }}', {
                                payment_method: {
                                    card: cardElement
                                }
                            }).then(function (confirmResult) {
                                if (confirmResult.error) {
                                    var errorElement = document.getElementById('card-errors');
                                    errorElement.textContent = confirmResult.error.message;
                                } else {
                                    // Payment successful
                                    // Handle success scenario, e.g. display success message, redirect to thank you page, etc.
                                }
                            });
                        });
</script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
