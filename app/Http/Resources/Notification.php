<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Notification extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'from' => LiteUser::make($this->from_user),
            'to' => LiteUser::make($this->to_user),
            'work' => Work::make($this->work),
            'like' => Like::make($this->like),
            'event' => Event::make($this->event),
            'circle' => Circle::make($this->circle),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'type_id' => $this->type_id,
            'status_id' => $this->status_id,
            'from_user_id' => $this->from_user_id,
            'to_user_id' => $this->to_user_id,
            'work_id' => $this->work_id,
            'like_id' => $this->like_id,
            'event_id' => $this->event_id,
            'circle_id' => $this->circle_id
        ];
    }
}
