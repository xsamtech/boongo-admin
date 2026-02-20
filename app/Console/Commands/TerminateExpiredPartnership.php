<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class TerminateExpiredPartnership extends Command
{
    // The name of the artisan command
    protected $signature = 'partnerships:terminate';

    // Command description
    protected $description = 'Terminate partnerships whose remaining days have reached 0';

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
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $terminated_status = Status::where([['status_name->fr', 'Terminé'], ['group_id', $partnership_status_group->id]])->first();

        if (!$terminated_status) {
            return;
        }

        // Check all partners for which the remaining days are <= 0
        $partners = Partner::with('categories') // Load related categories
                            ->get();

        foreach ($partners as $partner) {
            // Calculate the remaining days for each partner
            $remainingDays = $partner->remainingDays(Carbon::now());

            // If the remaining days are 0 or less, we terminate the partnership
            if ($remainingDays <= 0) {
                $categoryIds = $partner->categories()->pluck('categories.id')->toArray();

                // Update the partnership status to "Terminé"
                foreach ($categoryIds as $id) {
                    $partner->categories()->updateExistingPivot($id, [
                        'status_id' => $terminated_status->id
                    ]);
                }

                $this->info("Partnership with ID {$partner->id} terminated.");
            }
        }

        $this->info('Partnership verification completed.');
    }
}
