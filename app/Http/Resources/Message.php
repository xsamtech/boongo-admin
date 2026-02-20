<?php

namespace App\Http\Resources;

use App\Models\Group as ModelsGroup;
use App\Models\Type as ModelsType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Message extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group
        $file_type_group = ModelsGroup::where('group_name', 'Type de fichier')->first();
        // Types
        $doc_type = ModelsType::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $image_type = ModelsType::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $audio_type = ModelsType::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();

        return [
            'id' => $this->id,
            'message_content' => $this->message_content,
            'doc_title' => $this->doc_title,
            'doc_uri' => $this->doc_uri,
            'doc_page' => $this->doc_page,
            'answered_for' => $this->answered_for,
            'type' => Type::make($this->type),
            'status' => Status::make($this->status),
            'user' => LiteUser::make($this->user),
            'addressee_user' => LiteUser::make($this->addressee_user),
            'addressee_organization' => Organization::make($this->addressee_organization),
            'addressee_circle' => Circle::make($this->addressee_circle),
            'event' => Event::make($this->event),
            'likes' => Like::collection($this->likes),
            'documents' => File::collection($this->files->where('type_id', $doc_type->id)->values()),
            'images' => File::collection($this->files->where('type_id', $image_type->id)->values()),
            'audios' => File::collection($this->files->where('type_id', $audio_type->id)->values()),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_at_explicit' => $this->created_at->format('Y') == date('Y') ? explicitDayMonth($this->created_at->format('Y-m-d H:i:s')) : explicitDate($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_explicit' => $this->updated_at->format('Y') == date('Y') ? explicitDayMonth($this->updated_at->format('Y-m-d H:i:s')) : explicitDate($this->updated_at->format('Y-m-d H:i:s')),
            'created_at_ago' => timeAgo($this->created_at->format('Y-m-d H:i:s')),
            'updated_at_ago' => timeAgo($this->updated_at->format('Y-m-d H:i:s')),
            'addressee_organization_id' => $this->addressee_organization_id,
            'addressee_circle_id' => $this->addressee_circle_id,
            'addressee_user_id' => $this->addressee_user_id,
            'event_id' => $this->event_id
        ];
    }
}
