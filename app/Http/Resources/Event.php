<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_title' => $this->event_title,
            'event_description' => $this->event_description,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'start_at_explicit' => !empty($this->start_at) ? explicitDateTime($this->start_at->format('Y-m-d H:i:s')) : null,
            'end_at_explicit' => !empty($this->end_at) ? explicitDateTime($this->end_at->format('Y-m-d H:i:s')) : null,
            'event_place' => $this->event_place,
            'event_place_address' => $this->event_place_address,
            'cover_url' => !empty($this->cover_url) ? getWebURL() . $this->cover_url : getWebURL() . '/assets/img/banner-event.png',
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'organization' => Organization::make($this->organization),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'organization_id' => $this->organization_id
        ];
    }
}
