<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Program extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * ONE-TO-MANY
     * One course_year for several programs
     */
    public function course_year(): BelongsTo
    {
        return $this->belongsTo(CourseYear::class);
    }

    /**
     * ONE-TO-MANY
     * One organization for several programs
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a course_year
     */
    public function files(): HasMany
    {
        return $this->HasMany(File::class);
    }
}
