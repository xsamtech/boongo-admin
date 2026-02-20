<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Work extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    protected $casts = [
        'consultation_price' => 'decimal:2',
        'currency_id' => 'integer',
    ];

    /**
     * MANY-TO-MANY
     * Several sessions for several works
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'work_session')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot(['read']);
    }

    /**
     * MANY-TO-MANY
     * Several categories for several works
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * MANY-TO-MANY
     * Several carts for several works
     */
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class)->withTimestamps()->withPivot(['status_id']);
    }

    /**
     * ONE-TO-MANY
     * One currency for several works
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several works
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several works
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user_owner for several works
     */
    public function user_owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ONE-TO-MANY
     * One organization_owner for several works
     */
    public function organization_owner(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * ONE-TO-MANY
     * One category for several works
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * MANY-TO-ONE
     * Several favorites for a work
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'work_id');
    }

    /**
     * MANY-TO-ONE
     * Several likes for a work
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'for_work_id');
    }

    /**
     * MANY-TO-ONE
     * Several notifications for a work
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * MANY-TO-ONE
     * Several files for a work
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
