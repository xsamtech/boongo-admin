<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Currency extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'currencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Translatable properties.
     */
    protected $translatable = ['currency_name'];

    /**
     * MANY-TO-ONE
     * Several users for a currency
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several subscriptions for a currency
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * MANY-TO-ONE
     * Several works for a currency
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * MANY-TO-ONE
     * Several from_currencies for a currency
     */
    public function from_currencies(): HasMany
    {
        return $this->hasMany(CurrenciesRate::class, 'from_currency_id');
    }

    /**
     * MANY-TO-ONE
     * Several to_currencies for a currency
     */
    public function to_currencies(): HasMany
    {
        return $this->hasMany(CurrenciesRate::class, 'to_currency_id');
    }
}
