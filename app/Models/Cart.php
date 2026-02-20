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
class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several works for several carts
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class)->withTimestamps()->withPivot(['status_id']);
    }

    /**
     * MANY-TO-MANY
     * Several subscriptions for several carts
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class)->withTimestamps()->withPivot(['status_id']);
    }

    /**
     * ONE-TO-MANY
     * One status for several carts
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several carts
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ONE-TO-MANY
     * One payment for several carts
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Total price of consultation of works
     *
     * @return float|string
     */
    public function totalWorksConsultationsPrices($target_acronym): float|string
    {
        $target_currency = Currency::where('currency_acronym', $target_acronym)->first();

        if (is_null($target_currency)) {
            return __('miscellaneous.error_label') . ' ' . __('notifications.find_currency_404');
        }

        // One query for all necessary rates
        $currencies_rates = CurrenciesRate::where('to_currency_id', $target_currency->id)->orderByDesc('created_at')->get()->unique('from_currency_id');
        // Very fast memory access
        $rates_map = $currencies_rates->keyBy('from_currency_id');
        $total = 0;

        foreach ($this->works as $work) {
            $price = $work->consultation_price;
            $currency = $work->currency;

            if (!$price || !$currency) {
                continue;
            }

            if ($currency->id === $target_currency->id) {
                $total += $price;

            } else {
                // "$rates_map" allow us to access "$currencies_rates" query to get "from_currency_id"
                $currencies_rate = $rates_map[$currency->id] ?? null;

                if ($currencies_rate) {
                    $converted = $price * $currencies_rate->rate;
                    $total += $converted;

                } else {
                    return __('miscellaneous.error_label') . ' ' . __('notifications.find_currencies_rate_404');
                }
            }
        }

        return round($total, 2);
    }

    /**
     * Total price of subscriptions
     *
     * @return float|string
     */
    public function totalSubscriptionsPrices($target_acronym): float|string
    {
        $target_currency = Currency::where('currency_acronym', $target_acronym)->first();

        if (is_null($target_currency)) {
            return __('miscellaneous.error_label') . ' ' . __('notifications.find_currency_404');
        }

        // One query for all necessary rates
        $currencies_rates = CurrenciesRate::where('to_currency_id', $target_currency->id)->orderByDesc('created_at')->get()->unique('from_currency_id');
        // Very fast memory access
        $rates_map = $currencies_rates->keyBy('from_currency_id');
        $total = 0;

        foreach ($this->subscriptions as $subscription) {
            $price = $subscription->price;
            $currency = $subscription->currency;

            if (!$price || !$currency) {
                continue;
            }

            if ($currency->id === $target_currency->id) {
                $total += $price;

            } else {
                // "$rates_map" allow us to access "$currencies_rates" query to get "from_currency_id"
                $currencies_rate = $rates_map[$currency->id] ?? null;

                if ($currencies_rate) {
                    $converted = $price * $currencies_rate->rate;
                    $total += $converted;

                } else {
                    return __('miscellaneous.error_label') . ' ' . __('notifications.find_currencies_rate_404');
                }
            }
        }

        return round($total, 2);
    }
}
