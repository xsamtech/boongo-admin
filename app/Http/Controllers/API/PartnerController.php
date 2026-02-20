<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Partner;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Partner as ResourcesPartner;
use App\Http\Resources\User as ResourcesUser;
use App\Models\ActivationCode;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class PartnerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partners = Partner::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get inputs
        $inputs = [
            'name' => $request->name,
            'message' => $request->message,
            'from_user_id' => $request->from_user_id,
            'from_organization_id' => $request->from_organization_id,
            'website_url' => $request->website_url
        ];
        $partners = Partner::all();

        // Validate required fields
        if ($inputs['name'] == null OR $inputs['name'] == ' ') {
            return $this->handleError($inputs['name'], __('validation.required') . ' (' . __('miscellaneous.admin.partner.data.name') . ') ', 400);
        }

        // Check if partner name already exists
        foreach ($partners as $another_partner):
            if ($another_partner->name == $inputs['name']) {
                return $this->handleError($inputs['name'], __('validation.custom.name.exists'), 400);
            }
        endforeach;

        $partner = Partner::create($inputs);

        if ($request->image_64 != null) {
            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_path = 'images/partners/' . $partner->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::disk('public')->put($image_path, base64_decode($image));

            $partner->update([
                'image_url' => Storage::url($image_path),
                'updated_at' => now()
            ]);
        }

        if ($inputs['from_user_id'] != null) {
            $user = User::find($inputs['from_user_id']);

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if ($request->category_id != null) {
                if ($request->number_of_days == null) {
                    return $this->handleError(__('notifications.how_long_partnership'));
                }

                if ($request->entity == 'promotional') {
                    $random_int = random_int(1000, 9999);

                    $partner->categories()->attach($request->category_id, ['promo_code' => $random_int, 'number_of_days' => $request->number_of_days, 'status_id' => $active_status->id]);

                    if (!$user->hasRole('Partenaire')) {
                        $role = Role::where('role_name', 'Partenaire')->first();

                        $user->roles()->syncWithoutDetaching([$role->id]);
                    }

                } else if ($request->entity == 'activation') {
                    $codesCount = $request->codes_count ?? 1;
                    $codes = [];

                    for ($i = 0; $i < $codesCount; $i++) {
                        $codes[$request->category_id] = [
                            'activation_code' => Str::random(7),
                            'number_of_days' => $request->number_of_days,
                            'status_id' => $active_status->id
                        ];
                    }

                    $partner->categories()->attach($codes);

                    if (!$user->hasRole('Partenaire')) {
                        $role = Role::where('role_name', 'Partenaire')->first();

                        $user->roles()->syncWithoutDetaching([$role->id]);
                    }
                }

            } else {
                if (!$user->hasRole('Sponsor')) {
                    $role = Role::where('role_name', 'Sponsor')->first();

                    $user->roles()->syncWithoutDetaching([$role->id]);
                }
            }
        }

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.create_partner_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $partner = Partner::find($id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.find_partner_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner $partner)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'name' => $request->name,
            'message' => $request->message,
            'from_user_id' => $request->from_user_id,
            'from_organization_id' => $request->from_organization_id,
            'image_64' => $request->image_64,
            'website_url' => $request->website_url
        ];

        if ($inputs['name'] != null) {
            $partners = Partner::all();
            $current_partner = Partner::find($inputs['id']);

            if (is_null($current_partner)) {
                return $this->handleError(__('notifications.find_partner_404'));
            }
    
            foreach ($partners as $another_partner):
                if ($current_partner->name != $inputs['name']) {
                    if ($another_partner->name == $inputs['name']) {
                        return $this->handleError($inputs['name'], __('validation.custom.name.exists'), 400);
                    }
                }
            endforeach;

            $partner->update([
                'name' => $inputs['name'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['message'] != null) {
            $partner->update([
                'message' => $inputs['message'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['from_user_id'] != null) {
            $partner->update([
                'from_user_id' => $inputs['from_user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['from_organization_id'] != null) {
            $partner->update([
                'from_organization_id' => $inputs['from_organization_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['website_url'] != null) {
            $partner->update([
                'website_url' => $inputs['website_url'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['image_64'] != null) {
            $current_partner = Partner::find($inputs['id']);

            if (is_null($current_partner)) {
                return $this->handleError(__('notifications.find_partner_404'));
            }

            // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
            $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $inputs['image_64']);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/partners/' . $current_partner->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $partner->update([
                'image_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        if ($request->category_id != null) {
            if ($request->number_of_days == null) {
                return $this->handleError(__('notifications.how_long_partnership'));
            }

            if ($request->entity == 'promotional') {
                $random_int = random_int(1000, 9999);

                $partner->categories()->attach($request->category_id, ['promo_code' => $random_int, 'number_of_days' => $request->number_of_days, 'status_id' => $active_status->id]);

            } else if ($request->entity == 'activation') {
                $codesCount = $request->codes_count ?? 1;

                for ($i = 0; $i < $codesCount; $i++) {
                    $partner->categories()->newPivotStatement()->insert([
                        'category_id' => $request->category_id,
                        'partner_id' => $partner->id,
                        'activation_code' => Str::random(7),
                        'number_of_days' => $request->number_of_days,
                        'status_id' => $active_status->id
                    ]);
                }
            }
        }

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.update_partner_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Partner  $partner
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        $partners = Partner::all();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.delete_partner_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a partner
     *
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($data)
    {
        $partners = Partner::where('name', 'LIKE', '%' . $data . '%')->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * All partnerships according to status
     *
     * @param  string $locale
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function partnershipsByStatus($locale, $status_name)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $status = Status::where([['status_name->' . $locale, $status_name], ['group_id', $partnership_status_group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $partners = Partner::whereHas('categories', function ($query) use ($status) {
                                $query->where('category_partner.status_id', $status->id);
                            })->with(['categories' => function ($query) use ($status) {
                                $query->where('category_partner.status_id', $status->id);
                            }])->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * All partnerships according to status
     *
     * @param  string $locale
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function partnersWithActivationCode($locale, $status_name)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $status = Status::where([['status_name->' . $locale, $status_name], ['group_id', $partnership_status_group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $partners = Partner::whereHas('categories', function ($query) use ($status) {
                                $query->whereNotNull('category_partner.activation_code')->where('category_partner.status_id', $status->id);
                            })->get();

        return $this->handleResponse(ResourcesPartner::collection($partners), __('notifications.find_all_partners_success'));
    }

    /**
     * All users having promotional code of a specific partner
     *
     * @param  int $partner_id
     * @return \Illuminate\Http\Response
     */
    public function usersWithPromoCode($partner_id)
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
        $active_partnership = $partner->categories()->wherePivotNotNull('promo_code')->wherePivot('status_id', $active_status->id)->latest('updated_at')->first();

        if (is_null($active_partnership)) {
            return $this->handleError(__('notifications.find_active_partnership_404'));
        }

        // Access pivot data
        $pivot = $active_partnership->pivot;

        // Find all users having partner promotional code
        $users = User::where('promo_code', $pivot->promo_code)->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'));
    }

    /**
     * Withdraw some categories partnership.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function withdrawSomeCategories(Request $request, $id)
    {
        $partner = Partner::find($id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        $partner->categories()->detach($request->categories_ids);

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.update_partner_success'));
    }

    /**
     * Withdraw some categories partnership.
     *
     * @return \Illuminate\Http\Response
     */
    public function withdrawAllCategories($id)
    {
        $partner = Partner::find($id);

        if (is_null($partner)) {
            return $this->handleError(__('notifications.find_partner_404'));
        }

        $partner->categories()->detach();

        return $this->handleResponse(new ResourcesPartner($partner), __('notifications.update_partner_success'));
    }

    /**
     * Terminate partnership
     *
     * @param  int $partner_id
     * @return \Illuminate\Http\Response
     */
    public function terminatePartnership($partner_id)
    {
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $terminated_status = Status::where([['status_name->fr', 'Terminé'], ['group_id', $partnership_status_group->id]])->first();

        if (is_null($terminated_status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $partner = Partner::find($partner_id);

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

            // Withdraw promotional codes from all users
            if ($partner->categories[0]->pivot->promo_code != null) {
                $promoCode = $partner->categories[0]->pivot->promo_code;
                $users = User::where('promo_code', $promoCode)->get();

                if ($users->isNotEmpty()) {
                    foreach ($users as $user) {
                        $user->update([
                            'promo_code' => null,
                            'is_promoted' => null,
                        ]);
                    }
                }
            }

            return $this->handleResponse(new ResourcesPartner($partner), __('notifications.partnership_terminated'));

        // If days remaining are > 0, return a message indicating that it is not yet finished
        } else {
            return $this->handleError(__('notifications.partnership_still_active', ['remainingDays' => $remainingDays]));
        }
    }
}
