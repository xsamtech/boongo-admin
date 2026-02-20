<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class File extends JsonResource
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
            'file_name' => $this->file_name,
            'file_url' => $this->file_url != null ? $this->file_url : getWebURL() . '/assets/img/cover.png',
            'media_length' => $this->media_length,
            'type' => Type::make($this->type),
            'is_video' => $this->file_url != null ? isVideoFile($this->file_url) : false,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'type_id' => $this->type_id,
            'work_id' => $this->work_id,
            'program_id' => $this->program_id,
            'message_id' => $this->message_id
        ];
    }
}
