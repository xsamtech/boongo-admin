<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\ToxicContentController;
use App\Models\ToxicContent;
use Illuminate\Console\Command;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class UnlockUser extends Command
{
    // The name of the artisan command
    protected $signature = 'blockage:unlock';

    // Command description
    protected $description = 'Unblock a blockage that are past their due date';

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
        $toxic_content_controller = new ToxicContentController();
        // Check all users for which the remaining days are <= 0
        $toxic_contents = ToxicContent::where(['is_unlocked', 0])->get();

        foreach ($toxic_contents as $toxic_content) {
            // Try to invalidate the user subscription
            $toxic_content_controller->unlockUser($toxic_content->for_user_id);

            $this->info("Content with user ID: {$toxic_content->for_user_id} unlocked.");
        }

        $this->info('Unlock verification completed.');
    }
}
