<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InactiveSubscriptions implements ShouldQueue
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
        $dateTo = Carbon::now();
        $dateTo->subDays(30);

        $subsciptions = Subscription::where([
            'last_intent' => 1,
            'due_to' => $dateTo,
            'is_active' => 1,
        ])->update(['is_active' => 0]);
    }
}
