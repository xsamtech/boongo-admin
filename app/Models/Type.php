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
class Type extends Model
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
    protected $translatable = ['type_name'];

    /**
     * ONE-TO-MANY
     * One group for several types
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * MANY-TO-ONE
     * Several organizations for a type
     */
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * MANY-TO-ONE
     * Several events for a type
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * MANY-TO-ONE
     * Several works for a type
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * MANY-TO-ONE
     * Several subscriptions for a type
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * MANY-TO-ONE
     * Several payments for a type
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a type
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a type
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
