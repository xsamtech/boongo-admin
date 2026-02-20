<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Subscription extends JsonResource
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
            'number_of_hours' => $this->number_of_hours,
            'price' => formatIntegerNumber($this->price),
            'currency' => Currency::make($this->currency),
            'type' => Type::make($this->type),
            'category' => Category::make($this->category),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'currency' => $this->currency,
            'type_id' => $this->type_id,
            'category_id' => $this->category_id
        ];
    }
}
