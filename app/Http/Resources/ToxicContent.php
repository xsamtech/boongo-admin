<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ToxicContent extends JsonResource
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
            'is_reported' => $this->is_reported,
            'for_user_id' => $this->for_user_id,
            'for_work_id' => $this->for_work_id,
            'for_message_id' => $this->for_message_id,
            'explanation' => $this->explanation,
            'is_unlocked' => $this->is_unlocked,
            'is_archived' => $this->is_archived,
            'report_reason' => ReportReason::make($this->report_reason),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'report_reason_id' => $this->report_reason_id,
            'user_id' => $this->user_id
        ];
    }
}
