<?php

namespace App\Http\Controllers\API;

use App\Models\ActivationCode;
use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ActivationCode as ResourcesActivationCode;
use App\Http\Resources\User as ResourcesUser;
use Carbon\Carbon;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ActivationCodeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $activation_codes = ActivationCode::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesActivationCode::collection($activation_codes), __('notifications.find_all_activation_codes_success'));
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
            'for_partner_id' => $request->for_partner_id,
            'code' => $request->code,
            'is_active' => isset($request->is_active) ? $request->is_active : 1,
            'user_id' => $request->user_id
        ];

        $validator = Validator::make($inputs, [
            'code' => ['required'],
            'user_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $activation_code = ActivationCode::create($inputs);

        return $this->handleResponse(new ResourcesActivationCode($activation_code), __('notifications.create_activation_code_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $activation_code = ActivationCode::find($id);

        if (is_null($activation_code)) {
            return $this->handleError(__('notifications.find_activation_code_404'));
        }

        return $this->handleResponse(new ResourcesActivationCode($activation_code), __('notifications.find_activation_code_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ActivationCode  $activation_code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ActivationCode $activation_code)
    {
        // Get inputs
        $inputs = [
            'for_partner_id' => $request->for_partner_id,
            'code' => $request->code,
            'is_active' => $request->is_active,
            'user_id' => $request->user_id
        ];

        if ($inputs['for_partner_id'] != null) {
            $activation_code->update([
                'for_partner_id' => $inputs['for_partner_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['code'] != null) {
            $activation_code->update([
                'code' => $inputs['code'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_active'] != null) {
            $activation_code->update([
                'is_active' => $inputs['is_active'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $activation_code->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesActivationCode($activation_code), __('notifications.update_activation_code_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ActivationCode  $activation_code
     * @return \Illuminate\Http\Response
     */
    public function destroy(ActivationCode $activation_code)
    {
        $activation_code->delete();

        $activation_codes = ActivationCode::all();

        return $this->handleResponse(ResourcesActivationCode::collection($activation_codes), __('notifications.delete_activation_code_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * All users having some activation code belonging to specific a partner
     *
     * @param  int $user_id
     * @param  string $code
     * @param  string $partner_id
     * @return \Illuminate\Http\Response
     */
    public function findUsersByPartner($partner_id)
    {
        // Group
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Status
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Request
        $partner = Partner::find($partner_id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        // Ensure the partnership exists and is active
        $active_partnership_exists = $partner->categories()->wherePivot('status_id', $active_status->id)->exists();

        if (!$active_partnership_exists) {
            return $this->handleError(__('notifications.find_active_partnership_404'));
        }

        // Find all activation codes
        $activation_codes = ActivationCode::where('for_partner_id', $partner->id)->where('is_active', 1)->get();

        return $this->handleResponse(ResourcesActivationCode::collection($activation_codes), __('notifications.find_all_users_success'));
    }

    /**
     * Activate subscription directly via a activation code given by a partner
     *
     * @param  int $user_id
     * @param  string $code
     * @param  string $partner_id
     * @return \Illuminate\Http\Response
     */
    public function activateSubscription($user_id, $code, $partner_id)
    {
        // Group
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Status
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Request
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $partner = Partner::find($partner_id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        // Ensure the partner exists, is active and has activation code
        $category_by_activation_code = $partner->categories()->wherePivot('activation_code', $code)->wherePivot('is_used', 0)->wherePivot('status_id', $active_status->id)->first();

        if (is_null($category_by_activation_code)) {
            return $this->handleError(__('notifications.find_activation_code_404'));
        }

        $existing_activation_code = ActivationCode::where('for_partner_id', $partner->id)->where('code', $code)->first();

        if (!empty($existing_activation_code)) {
            return $this->handleError(__('notifications.existing_activation_code'));
        }

        // Register user activation code
        $activation_code = ActivationCode::create([
            'for_partner_id' => $partner->id,
            'code' => $code,
            'is_active' => 1,
            'user_id' => $user->id
        ]);

        $partner->categories()->wherePivot('activation_code', $code)->updateExistingPivot($category_by_activation_code->id, [
            'is_used' => 1
        ]);

        return $this->handleResponse(new ResourcesActivationCode($activation_code), __('notifications.create_subscription_success'));
    }

    /**
     * Disable subscription got via a activation code given by a partner
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function disableSubscription($user_id)
    {
        // Group
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Status
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        $terminated_status = Status::where([['status_name->fr', 'Terminé'], ['group_id', $partnership_status_group->id]])->first();
        // Request
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $user_activation_code = $user->activation_codes()->where('is_active', 1)->latest('updated_at')->first();

        if (is_null($user_activation_code)) {
            return $this->handleError(__('notifications.find_activation_code_404'));
        }

        // Ensure the partner exists, is active and has activation code
        $partner = Partner::whereHas('categories', function ($query) use ($active_status, $user_activation_code) {
                                $query->where('category_partner.status_id', $active_status->id)
                                        ->where('category_partner.activation_code', $user_activation_code->code);
                            })->first();

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        // Calculate the remaining days for the partnership
        $remainingDays = $partner->remainingDays(Carbon::now());

        // If the remaining days are 0, we end the partnership
        if ($remainingDays <= 0) {
            $categoryIds = $partner->categories()->pluck('categories.id')->toArray();

            // Update all records in the "category_partner" table for this partner
            foreach ($categoryIds as $id) {
                $partner->categories()->updateExistingPivot($id, [
                    'status_id' => $terminated_status->id
                ]);
            }

            // Disable all activation codes
            $activationCodes = ActivationCode::where('for_partner_id', $partner->id)->get();

            if ($activationCodes->isNotEmpty()) {
                foreach ($activationCodes as $activationCode) {
                    $activationCode->update(['is_active' => 0]);
                }
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.update_subscription_success'));

        // If days remaining are > 0, return a message indicating that it is not yet finished
        } else {
            return $this->handleResponse(new ResourcesUser($user), __('notifications.partnership_still_active', ['remainingDays' => $remainingDays]));
        }
    }
}
