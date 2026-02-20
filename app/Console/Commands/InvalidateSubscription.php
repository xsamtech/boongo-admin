<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\SubscriptionController;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class InvalidateSubscription extends Command
{
    // The name of the artisan command
    protected $signature = 'subscriptions:invalidate';

    // Command description
    protected $description = 'Invalidate subscriptions that are past their due date';

    /**
     * Create a command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     */
    public function handle(): void
    {
        $subscription_controller = new SubscriptionController();
        // Check all users for which the remaining days are <= 0
        $users = User::with('subscriptions') // Load related subscriptions
                            ->get();

        foreach ($users as $user) {
            // Try to invalidate the user subscription
            $subscription_controller->invalidateSubscription($user->id);

            $this->info("Subscription with user ID: {$user->id} invalidated.");
        }

        $this->info('Subscription verification completed.');
    }
}
