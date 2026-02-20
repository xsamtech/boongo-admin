<?php

namespace App\Http\Controllers\API;

use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Resources\Currency as ResourcesCurrency;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CurrencyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = Currency::all();

        return $this->handleResponse(ResourcesCurrency::collection($currencies), __('notifications.find_all_currencies_success'));
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
            'currency_name' => [
                'en' => $request->currency_name_en,
                'fr' => $request->currency_name_fr,
                'ln' => $request->currency_name_ln
            ],
            'currency_acronym' => $request->currency_acronym,
            'currency_icon' => $request->currency_icon
        ];
        // Select all currencies to check unique constraint
        $currencies = Currency::all();

        // Validate required fields
        if ($inputs['currency_name'] == null) {
            return $this->handleError($inputs['currency_name'], __('validation.required'), 400);
        }

        if ($inputs['currency_acronym'] == null) {
            return $this->handleError($inputs['currency_acronym'], __('validation.required'), 400);
        }

        // Check if currency name already exists
        foreach ($currencies as $another_currency):
            if ($another_currency->currency_name == $inputs['currency_name']) {
                return $this->handleError($inputs['currency_name'], __('validation.custom.name.exists'), 400);
            }
        endforeach;

        $currency = Currency::create($inputs);

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.create_currency_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $currency = Currency::find($id);

        if (is_null($currency)) {
            return $this->handleError(__('notifications.find_currency_404'));
        }

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.find_currency_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency)
    {
        // Get inputs
        $inputs = [
            'currency_name' => [
                'en' => $request->currency_name_en,
                'fr' => $request->currency_name_fr,
                'ln' => $request->currency_name_ln
            ],
            'currency_acronym' => $request->currency_acronym,
            'currency_icon' => $request->currency_icon
        ];

        if ($inputs['currency_name'] != null) {
            // Select all currencies and specific currency to check unique constraint
            $currencies = Currency::all();
            $current_currency = Currency::find($inputs['id']);

            foreach ($currencies as $another_currency):
                if ($current_currency->currency_name != $inputs['currency_name']) {
                    if ($another_currency->currency_name == $inputs['currency_name']) {
                        return $this->handleError($inputs['currency_name'], __('validation.custom.name.exists'), 400);
                    }
                }
            endforeach;

            $currency->update([
                'currency_name' => [
                    'en' => $request->currency_name_en,
                    'fr' => $request->currency_name_fr,
                    'ln' => $request->currency_name_ln
                ],
                'updated_at' => now()
            ]);
        }

        if ($inputs['currency_acronym'] != null) {
            $currency->update([
                'currency_acronym' => $inputs['currency_acronym'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['currency_icon'] != null) {
            $currency->update([
                'currency_icon' => $inputs['currency_icon'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCurrency($currency), __('notifications.update_currency_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();

        $currencys = Currency::all();

        return $this->handleResponse(ResourcesCurrency::collection($currencys), __('notifications.delete_currency_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a currency by name.
     *
     * @param  string $locale
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($locale, $data)
    {
        $currencies = Currency::where('currency_name->' . $locale, $data)->get();

        return $this->handleResponse(ResourcesCurrency::collection($currencies), __('notifications.find_all_currencies_success'));
    }
}
