<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Currency extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency_name' => $this->currency_name,
            'currency_name_en' => $this->getTranslation('currency_name', 'en'),
            'currency_name_fr' => $this->getTranslation('currency_name', 'fr'),
            'currency_name_ln' => $this->getTranslation('currency_name', 'ln'),
            'currency_acronym' => $this->currency_acronym,
            'currency_icon' => $this->currency_icon,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
