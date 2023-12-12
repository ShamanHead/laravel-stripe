<x-app-layout>
    <div class="flex justify-center items-center h-screen">
        <div class="text-center">
            <svg id="success-icon" viewBox="0 0 24 24" class="w-16 h-16 text-green-500 mx-auto hidden">
                <path fill="currentColor"
                    d="M11 19.218L4.929 13l-1.414 1.414L11 22.046l12.485-12.414L21.07 10l-10 9.999z" />
            </svg>
            <svg id="loading-icon" viewBox="0 0 24 24" class="w-16 h-16 text-blue-500 mx-auto hidden animate-spin">
                <svg id="loading-icon" viewBox="0 0 24 24" class="w-16 h-16 text-blue-500 mx-auto hidden animate-spin">
                    <circle cx="12" cy="12" r="10" fill="none" stroke-width="4" class="opacity-25" />
                    <circle cx="12" cy="12" r="10" fill="none" stroke-width="4" class="opacity-50" />
                    <circle cx="12" cy="12" r="10" fill="none" stroke-width="4" class="opacity-75" />
                </svg>
            </svg>
            <svg id="failed-icon" viewBox="0 0 24 24" class="w-16 h-16 text-red-500 mx-auto hidden">
                <svg id="failed-icon" viewBox="0 0 24 24" class="w-16 h-16 text-red-500 mx-auto hidden">
                    <path fill="currentColor"
                        d="M19.07 5l-8.249 8.249L2.591 5 .5 7.091l8.249 8.249L.5 23.591 2.591 25.5 10.84 17.251 19.09 25.5l2.091-2.091-8.249-8.249L23.591 5z" />
                </svg>
            </svg>
            <h1 id="status-text" class="text-3xl font-bold mt-8">Payment In Process</h1>
            <a href="{{ route('subscription') }}" id="subscription-link"
                class="inline-block mt-4 px-6 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Go to Subscriptions
            </a>
        </div>
    </div>
    <script>
        const checkPaymentStatus = () => {
            let counter = 0;
            const timer = setInterval(() => {
                if (counter >= 5) {
                    clearInterval(timer);
                    document.getElementById('loading-icon').classList.add('hidden');
                    document.getElementById('failed-icon').classList.remove('hidden');
                    document.getElementById('status-text').textContent = 'Payment failed';
                } else {
                    fetch('/api/payment/check/{{ $id }}')
                        .then(response => response.text())
                        .then(result => {
                            if (result === 'OK') {
                                clearInterval(timer);
                                document.getElementById('loading-icon').classList.add('hidden');
                                document.getElementById('success-icon').classList.remove('hidden');
                                document.getElementById('status-text').textContent = 'Payment Completed';
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    counter++;
                }
            }, 1000);
        };

        checkPaymentStatus();
    </script>
</x-app-layout>
