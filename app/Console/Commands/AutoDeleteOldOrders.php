<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class AutoDeleteOldOrders extends Command
{
    protected $signature = 'orders:autodelete';
    protected $description = 'Auto soft-delete completed or cancelled orders older than 2 weeks';

    public function handle()
    {
        $thresholdDate = Carbon::now()->subWeeks(2);

        $deleted = Order::whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED
            ])
            ->where('created_at', '<', $thresholdDate)
            ->delete();

        $this->info("Auto-deleted $deleted orders.");
    }
}
