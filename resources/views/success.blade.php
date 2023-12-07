<x-app-layout>
    <div class="flex justify-center items-center h-screen">
        <div class="text-center">
            <svg viewBox="0 0 24 24" class="w-16 h-16 text-green-500 mx-auto">
                <path fill="currentColor"
                    d="M11 19.218L4.929 13l-1.414 1.414L11 22.046l12.485-12.414L21.07 10l-10 9.999z" />
            </svg>
            <h1 class="text-3xl font-bold mt-8">Payment Completed</h1>
            <a href="{{ route('subscription') }}"
                class="inline-block mt-4 px-6 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Go to Subscriptions
            </a>
        </div>
    </div>
</x-app-layout>
