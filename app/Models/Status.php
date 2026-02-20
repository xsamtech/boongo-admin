<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Status extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Translatable properties.
     */
    protected $translatable = ['status_name'];

    /**
     * ONE-TO-MANY
     * One group for several statuses
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several users for a status
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several organizations for a status
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * MANY-TO-ONE
     * Several events for a status
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * MANY-TO-ONE
     * Several payments for a status
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a status
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
