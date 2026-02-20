<?php

namespace App\Events;

use App\Http\Resources\Message as ResourcesMessage;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->message?->load([
            'files',
            'type.group',
            'status.group',
            'user.country',
            'user.currency',
            'user.status.group',
            'user.roles',
            'user.toxic_contents.report_reason',
            'likes.user.country',
            'likes.user.currency',
            'likes.user.status.group',
            'likes.user.roles',
            'likes.user.toxic_contents.report_reason',
        ]);

        Log::info('📤 Event MessageSent créé', [
            'message_id' => $message->id,
            'to_channel' => 'chat.' . $message->addressee_user_id,
            'from_user_id' => $message->user_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->message?->addressee_user_id) {
            $channels[] = new PrivateChannel('chat.' . $this->message?->addressee_user_id);

            // Log::info('📡 Diffusion sur canal', ['channel' => 'private-chat.' . $this->message?->addressee_user_id]);
            Log::info('📡 Diffusion sur canal', ['channel' => 'private-chat.' . $this->message?->addressee_user_id, 'data' => $this->message?->message_content]);
        }

        if ($this->message?->addressee_circle_id) {
            $channels[] = new PrivateChannel('circle.' . $this->message?->addressee_circle_id);

            Log::info('📡 Diffusion sur canal', ['channel' => 'private-chat.' . $this->message?->addressee_circle_id]);
        }

        if ($this->message?->addressee_organization_id) {
            $channels[] = new PrivateChannel('organization.' . $this->message?->addressee_organization_id);

            Log::info('📡 Diffusion sur canal', ['channel' => 'private-chat.' . $this->message?->addressee_organization_id]);
        }

        if ($this->message?->event_id) {
            $channels[] = new PrivateChannel('event.' . $this->message?->event_id);

            Log::info('📡 Diffusion sur canal', ['channel' => 'private-chat.' . $this->message?->event_id]);
        }

        return $channels;
    }

    /**
     * Le nom personnalisé de l’événement broadcasté (facultatif, mais conseillé si tu veux éviter le nom de classe complet).
     */
    public function broadcastAs()
    {
        return 'MessageSent';
    }

    /**
     * Data structure sent to the front end app.
     */
    public function broadcastWith()
    {
        return [
            'message' => new ResourcesMessage($this->message->load(['type.group', 'status.group', 'user.country', 'user.currency', 'user.status.group', 'files', 'likes.user'])),
        ];
    }
}
