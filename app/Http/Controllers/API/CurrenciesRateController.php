<?php

namespace App\Http\Controllers\API;

use App\Models\CurrenciesRate;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CurrenciesRate as ResourcesCurrenciesRate;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CurrenciesRateController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies_rates = CurrenciesRate::all();

        return $this->handleResponse(ResourcesCurrenciesRate::collection($currencies_rates), __('notifications.find_all_currencies_rates_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'rate' => $request->rate,
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id' => $request->to_currency_id,
        ];
        $currencies_rates = CurrenciesRate::all();

        $validator = Validator::make($inputs, [
            'rate' => ['required'],
            'from_currency_id' => ['required'],
            'to_currency_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        if ($inputs['from_currency_id'] == $inputs['to_currency_id']) {
            return $this->handleError($inputs['from_currency_id'], __('validation.custom.currency.repetition'), 400);
        }

        // Check if rate already exists
        foreach ($currencies_rates as $another_currencies_rate):
            if ($another_currencies_rate->from_currency_id == $inputs['from_currency_id'] && $another_currencies_rate->to_currency_id == $inputs['to_currency_id']) {
                return $this->handleError($inputs['from_currency_id'] . ' & ' . $inputs['to_currency_id'], __('validation.custom.currency.exists'), 400);
            }
        endforeach;

        $currencies_rate = CurrenciesRate::create($inputs);

        return $this->handleResponse(new ResourcesCurrenciesRate($currencies_rate), __('notifications.create_currencies_rate_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $currencies_rate = CurrenciesRate::find($id);

        if (is_null($currencies_rate)) {
            return $this->handleError(__('notifications.find_currencies_rate_404'));
        }

        return $this->handleResponse(new ResourcesCurrenciesRate($currencies_rate), __('notifications.find_currencies_rate_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CurrenciesRate $currencies_rate)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'rate' => $request->rate,
            'from_currency_id' => $request->from_currency_id,
            'to_currency_id' => $request->to_currency_id,
        ];

        if ($inputs['rate'] != null) {
            $currencies_rate->update([
                'rate' => $inputs['rate'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['from_currency_id'] != null) {
            $currencies_rates = CurrenciesRate::all();
            $current_currencies_rate = CurrenciesRate::find($inputs['id']);

            if (is_null($current_currencies_rate)) {
                return $this->handleError(__('notifications.find_currencies_rate_404'));
            }

            if ($inputs['from_currency_id'] == $current_currencies_rate->to_currency_id) {
                return $this->handleError($inputs['from_currency_id'], __('validation.custom.currency.repetition'), 400);
            }

            foreach ($currencies_rates as $another_currencies_rate):
                if ($current_currencies_rate->from_currency_id != $inputs['from_currency_id']) {
                    if ($another_currencies_rate->from_currency_id == $inputs['from_currency_id'] && $another_currencies_rate->to_currency_id == $current_currencies_rate->to_currency_id) {
                        return $this->handleError($inputs['from_currency_id'] . ' & ' . $current_currencies_rate->to_currency_id, __('validation.custom.currency.exists'), 400);
                    }
                }
            endforeach;

            $currencies_rate->update([
                'from_currency_id' => $inputs['from_currency_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['to_currency_id'] != null) {
            $currencies_rates = CurrenciesRate::all();
            $current_currencies_rate = CurrenciesRate::find($inputs['id']);

            if (is_null($current_currencies_rate)) {
                return $this->handleError(__('notifications.find_currencies_rate_404'));
            }

            if ($inputs['to_currency_id'] == $current_currencies_rate->from_currency_id) {
                return $this->handleError($inputs['to_currency_id'], __('validation.custom.currency.repetition'), 400);
            }

            foreach ($currencies_rates as $another_currencies_rate):
                if ($current_currencies_rate->to_currency_id != $inputs['to_currency_id']) {
                    if ($another_currencies_rate->to_currency_id == $inputs['to_currency_id'] && $another_currencies_rate->from_currency_id == $current_currencies_rate->from_currency_id) {
                        return $this->handleError($current_currencies_rate->from_currency_id . ' & ' . $inputs['to_currency_id'], __('validation.custom.currency.exists'), 400);
                    }
                }
            endforeach;

            $currencies_rate->update([
                'to_currency_id' => $inputs['to_currency_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCurrenciesRate($currencies_rate), __('notifications.update_currencies_rate_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CurrenciesRate  $currencies_rate
     * @return \Illuminate\Http\Response
     */
    public function destroy(CurrenciesRate $currencies_rate)
    {
        $currencies_rate->delete();

        $currencies_rates = CurrenciesRate::all();

        return $this->handleResponse(ResourcesCurrenciesRate::collection($currencies_rates), __('notifications.delete_currencies_rate_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find currency rate.
     *
     * @param  string $from_currency_acronym
     * @param  string $to_currency_acronym
     * @return \Illuminate\Http\Response
     */
    public function findCurrencyRate($from_currency_acronym, $to_currency_acronym)
    {
        // Currencies
        $from_currency = Currency::where('currency_acronym', $from_currency_acronym)->first();
        $to_currency = Currency::where('currency_acronym', $to_currency_acronym)->first();
        // Request
        $currencies_rate = CurrenciesRate::where([['from_currency_id', $from_currency->id], ['to_currency_id', $to_currency->id]])->first();

        if (is_null($currencies_rate)) {
            return $this->handleError(__('notifications.find_currencies_rate_404'));
        }

        return $this->handleResponse(new ResourcesCurrenciesRate($currencies_rate), __('notifications.find_currencies_rate_success'));
    }
}
