<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several carts for several subscriptions
     */
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class)->withTimestamps()->withPivot(['status_id']);
    }

    /**
     * ONE-TO-MANY
     * One currency for several subscriptions
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * ONE-TO-MANY
     * One type for several subscriptions
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One category for several subscriptions
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
