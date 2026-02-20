<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ToxicContent extends Model
{
    use HasFactory;

    protected $table = 'toxic_contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * ONE-TO-MANY
     * One report_reason for several toxic_contents
     */
    public function report_reason(): BelongsTo
    {
        return $this->belongsTo(ReportReason::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several toxic_contents
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the remaining days since blocking.
     *
     * @param \Carbon\Carbon $date
     * @return int
     */
    public function getRemainingDaysAttribute()
    {
        $createdAt = Carbon::parse($this->created_at);
        $today = Carbon::today();

        return $today->diffInDays($createdAt, false);
    }
}
