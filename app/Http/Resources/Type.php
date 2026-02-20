<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Type extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type_name' => $this->alias == 'unique_subscription' ? ($this->type_name . ' (24h)') : $this->type_name,
            'type_name_en' => $this->alias == 'unique_subscription' ? ($this->getTranslation('type_name', 'en') . ' (24h)') : $this->getTranslation('type_name', 'en'),
            'type_name_fr' => $this->alias == 'unique_subscription' ? ($this->getTranslation('type_name', 'fr') . ' (24h)') : $this->getTranslation('type_name', 'fr'),
            'type_name_ln' => $this->alias == 'unique_subscription' ? ($this->getTranslation('type_name', 'ln') . ' (24h)') : $this->getTranslation('type_name', 'ln'),
            'type_description' => $this->type_description,
            'alias' => $this->alias,
            'icon' => $this->icon,
            'group' => Group::make($this->group),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'group_id' => $this->group_id,
        ];
    }
}
