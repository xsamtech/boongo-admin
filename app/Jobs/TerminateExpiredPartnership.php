<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class TerminateExpiredPartnership implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Number of attempts in case of failure
    public $tries = 1;

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

                // Update all records in the "category_partner" table for this partner
                foreach ($categoryIds as $id) {
                    $partner->categories()->updateExistingPivot($id, [
                        'status_id' => $terminated_status->id
                    ]);
                }
            }
        }
    }
}
