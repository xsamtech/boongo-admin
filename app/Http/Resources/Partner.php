<?php

namespace App\Http\Resources;

use App\Models\User as ModelsUser;
use App\Models\Organization as ModelsOrganization;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Partner extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $from_user = $this->from_user_id != null ? ModelsUser::where('id', $this->from_user_id)->first() : null;
        $from_organization = $this->from_organization_id != null ? ModelsOrganization::where('id', $this->from_organization_id)->first() : null;
        // Call the remainingDays() function with the current date
        $remainingDays = $this->remainingDays(Carbon::now());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'message' => $this->message,
            'from_user' => !empty($from_user) ? LiteUser::make($from_user) : null,
            'from_organization' => !empty($from_organization) ? Organization::make($from_organization) : null,
            'image_url' => $this->image_url != null ? getWebURL() . $this->image_url : getWebURL() . '/public/assets/img/ad.png',
            'website_url' => $this->website_url,
            'remaining_days' => $remainingDays,
            'has_promo_code' => $this->hasPromoCode(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
