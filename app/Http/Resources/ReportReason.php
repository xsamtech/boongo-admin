<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ReportReason extends JsonResource
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
            'reason_content' => $this->reason_content,
            'reason_content_en' => $this->getTranslation('reason_content', 'en'),
            'reason_content_fr' => $this->getTranslation('reason_content', 'fr'),
            'reason_content_ln' => $this->getTranslation('reason_content', 'ln'),
            'reports_count' => $this->reports_count,
            'blocked_for' => $this->blocked_for,
            'entity' => $this->entity,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}
