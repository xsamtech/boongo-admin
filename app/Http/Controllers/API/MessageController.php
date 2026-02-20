<?php

namespace App\Http\Controllers\API;

use App\Models\Circle;
use App\Models\Event;
use App\Models\File;
use App\Models\Group;
use App\Models\Like;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Message as ResourcesMessage;
use App\Http\Resources\Partner as ResourcesPartner;
use App\Http\Resources\User as ResourcesUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class MessageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Groups
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        $message_type_group = Group::where('group_name', 'Type de message')->first();
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Statuses
        $unread_message_status = Status::where([['status_name->fr', 'Non lu'], ['group_id', $message_status_group->id]])->first();
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $message_in_organisation_type = Type::where([['type_name->fr', 'Message dans l\'organisation'], ['group_id', $notification_type_group->id]])->first();
        $chat_type = Type::where([['type_name->fr', 'Discussion'], ['group_id', $message_type_group->id]])->first();
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'message_content' => $request->message_content,
            'doc_title' => $request->doc_title,
            'doc_uri' => $request->doc_uri,
            'answered_for' => $request->answered_for,
            'type_id' => isset($request->type_id) ? $request->type_id : $chat_type->id,
            'status_id' => isset($request->status_id) ? $request->status_id : $unread_message_status->id,
            'user_id' => $request->user_id,
            'addressee_user_id' => $request->addressee_user_id,
            'addressee_organization_id' => $request->addressee_organization_id,
            'addressee_circle_id' => $request->addressee_circle_id,
            'event_id' => $request->event_id
        ];

        // Validate required fields
        if ($inputs['message_content'] == null OR $inputs['message_content'] == ' ') {
            return $this->handleError($inputs['message_content'], __('validation.required'), 400);
        }

        if ($inputs['user_id'] == null OR $inputs['user_id'] == ' ') {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        $message_sender = User::find($inputs['user_id']);

        if (is_null($message_sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $message = Message::create($inputs);

        if ($request->hasFile('files_urls')) {
            // Types of extensions for different file types
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'mkv', 'webm'];
            $document_extensions = ['pdf', 'doc', 'docx', 'txt'];
            $audio_extensions = ['mp3', 'wav', 'flac'];

            // File browsing
            foreach ($request->file('files_urls') as $key => $singleFile) {
                // Checking the file extension
                $file_extension = $singleFile->getClientOriginalExtension();

                // File type check
                $custom_uri = '';
                $is_valid_type = false;
                $file_type_id = null;

                if (in_array($file_extension, $image_extensions)) { // File is an image
                    $custom_uri = 'images/messages';
                    $file_type_id = $image_type->id;
                    $is_valid_type = true;

                } elseif (in_array($file_extension, $document_extensions)) { // File is a document
                    $custom_uri = 'documents/messages';
                    $file_type_id = $document_type->id;
                    $is_valid_type = true;

                } elseif (in_array($file_extension, $audio_extensions)) { // File is an audio
                    $custom_uri = 'audios/messages';
                    $file_type_id = $audio_type->id;
                    $is_valid_type = true;
                }

                // If the extension does not match any valid type
                if (!$is_valid_type) {
                    return $this->handleError(__('notifications.type_is_not_file'));
                }

                // Generate a unique path for the file
                $filename = $singleFile->getClientOriginalName();
                $file_path = $custom_uri . '/' . $message->id . '/' . Str::random(50) . '.' . $file_extension;

                // Upload file
                Storage::disk('public')->put($file_path, $singleFile);

                // Creating the database record for the file
                File::create([
                    'file_name' => trim($request->files_names[$key]) ?: $filename,
                    'file_url' => Storage::url($file_path),
                    'type_id' => $file_type_id,
                    'message_id' => $message->id
                ]);
            }
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        // If the message is sent to the organization, notify to all organization members
        if ($inputs['addressee_organization_id'] != null) {
            $addressee_organization = Organization::find($inputs['addressee_organization_id']);

            if (is_null($addressee_organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $organization_users = $addressee_organization->users;
            $other_organization_messages = Message::whereNotNull('answered_for')->where('addressee_organization_id', $inputs['addressee_organization_id'])->get();

            // If there is no other message to the organisation, send notification
            if ($other_organization_messages == null) {
                foreach ($organization_users as $user):
                    Notification::create([
                        'type_id' => $message_in_organisation_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $message_sender->id,
                        'to_user_id' => $user->id,
                        'message_id' => $message->id
                    ]);
                endforeach;
            }
        }

        $message->load(['user', 'addressee_user', 'addressee_organization', 'addressee_circle', 'event']);
        // Load relations for the "MessageSent" event
        $message->load(['user.roles', 'user.country', 'user.currency', 'user.status.group', 'type.group', 'status.group', 'files', 'likes.user.roles', 'likes.user.country', 'likes.user.currency', 'likes.user.status.group', 'likes.user.toxic_contents.report_reason']);

        // Check that the events are broadcasted correctly
        Log::info('Broadcasting message ID ' . $message->id);
        // The event broadcast
        broadcast(new MessageSent($message))->toOthers();

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.create_message_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // Group
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Request
        $message = Message::find($id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        // If the message is sent to the organization or circle, identify the user who saw it
        if ($message->addressee_organization_id != null OR $message->addressee_circle_id != null) {
            if ($request->hasHeader('X-user-id')) {
                $user = User::find($request->header('X-user-id'));

                if (!is_null($user)) {
                    $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
                }
            }
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        // Get inputs
        $inputs = [
            'message_content' => $request->message_content,
            'doc_title' => $request->doc_title,
            'doc_uri' => $request->doc_uri,
            'answered_for' => $request->answered_for,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'addressee_user_id' => $request->addressee_user_id,
            'addressee_organization_id' => $request->addressee_organization_id,
            'addressee_circle_id' => $request->addressee_circle_id,
            'event_id' => $request->event_id
        ];

        if ($inputs['message_content'] != null) {
            $message->update([
                'message_content' => $inputs['message_content'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['doc_title'] != null) {
            $message->update([
                'doc_title' => $inputs['doc_title'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['doc_uri'] != null) {
            $message->update([
                'doc_uri' => $inputs['doc_uri'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['answered_for'] != null) {
            $message->update([
                'answered_for' => $inputs['answered_for'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['type_id'] != null) {
            $message->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $message->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $message->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_user_id'] != null) {
            $message->update([
                'addressee_user_id' => $inputs['addressee_user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_organization_id'] != null) {
            $message->update([
                'addressee_organization_id' => $inputs['addressee_organization_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['addressee_circle_id'] != null) {
            $message->update([
                'addressee_circle_id' => $inputs['addressee_circle_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['event_id'] != null) {
            $message->update([
                'event_id' => $inputs['event_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.update_message_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        $message->delete();

        $messages = Message::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.delete_message_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a message in chat.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  string $data
     * @param  int $sender_id
     * @param  int $addressee_id
     * @return \Illuminate\Http\Response
     */
    public function searchInChat($locale, $type_name, $data, $sender_id, $addressee_id)
    {
        // Requests
        $message_type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($message_type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $sender = User::find($sender_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $addressee = User::find($addressee_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->orderByDesc('created_at')->paginate(10);
        $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orWhere([['message_content', 'LIKE', '%' . $data . '%'], ['type_id', $message_type->id], ['user_id', $addressee->id], ['addressee_user_id', $sender->id]])->count();

        if (is_null($messages)) {
            return $this->handleResponse([], __('miscellaneous.empty_list'));
        }

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
    }

    /**
     * Search a message in group (organization or circle).
     *
     * @param  string $entity
     * @param  int $entity_id
     * @param  int $member_id
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function searchInGroup($entity, $entity_id, $member_id, $data)
    {
        // Requests
        $member = User::find($member_id);

        if (is_null($member)) {
            return $this->handleError(__('notifications.find_member_404'));
        }

        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_organization_id', $organization->id]])->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_organization_id', $organization->id]])->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_circle_id', $circle->id]])->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where([['message_content', 'LIKE', '%' . $data . '%'], ['addressee_circle_id', $circle->id]])->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }

    /**
     * Search a message in group (organization or circle).
     *
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function findByGroup($entity, $entity_id)
    {
        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $messages = Message::where('addressee_organization_id', $organization->id)->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where('addressee_organization_id', $organization->id)->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $messages = Message::where('addressee_circle_id', $circle->id)->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where('addressee_circle_id', $circle->id)->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'event') {
            $event = Event::find($entity_id);

            if (is_null($event)) {
                return $this->handleError(__('notifications.find_event_404'));
            }

            $messages = Message::where('event_id', $event->id)->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where('event_id', $event->id)->count();

            if (is_null($messages)) {
                return $this->handleResponse([], __('miscellaneous.empty_list'));
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }

    /**
     * GET: Display user conversations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $locale
     * @param  string $type_name
     * @param  int $user_id
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function userChatsList(Request $request, $locale, $type_name, $user_id)
    {
        // Groups
        $invitation_status_group = Group::where('group_name', 'Etat de l\'invitation')->first();
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $message_type_group = Group::where('group_name', 'Type de message')->first();
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        // Status
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        $accepted_status = Status::where([['status_name->fr', 'Acceptée'], ['group_id', $invitation_status_group->id]])->first();

        // Requests
        $status = null;

        if (isset($request->status_name) && is_string($request->status_name) && !empty($request->status_name)) {
            $status = Status::where([['status_name->' . $locale, $request->status_name], ['group_id', $message_status_group->id]])->first();

            if (is_null($status)) return $this->handleError(__('notifications.find_status_404'));
        }

        $users_ids = User::whereHas('roles', function ($query) {
                                $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor');
                            })->pluck('id')->toArray();
        $type = Type::where([['type_name->' . $locale, $type_name], ['group_id', $message_type_group->id]])->first();

        if (is_null($type)) return $this->handleError(__('notifications.find_type_404'));

        $user = User::find($user_id);

        if (is_null($user)) return $this->handleError(__('notifications.find_user_404'));

        $is_subscribed = $user->hasValidSubscription();
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $discussions = collect();

        // === DIRECT USER MESSAGE ===
        $userGroups = Message::whereNotNull('addressee_user_id')
                                ->where('type_id', $type->id)
                                ->where(function ($q) use ($user) {
                                    $q->where('user_id', $user->id)
                                    ->orWhere('addressee_user_id', $user->id);
                                })
                                ->when($status, function ($query) use ($status) {
                                    $query->whereHas('status', function($q) use ($status) {
                                        $q->where('status_id', $status->id);
                                    });
                                })
                                ->with('addressee_user', 'user')
                                ->get()
                                ->groupBy(function ($message) use ($user) {
                                    return $message->user_id == $user->id ? $message->addressee_user_id : $message->user_id;
                                });

        foreach ($userGroups as $messages) {
            $filtered = $messages->filter(
                fn($msg) => $msg->user_id == $user->id || $msg->addressee_user_id == $user->id
            );

            if ($filtered->isEmpty()) continue;

            $latest = $filtered->sortByDesc('created_at')->first();
            $correspondent = $latest->user_id == $user->id ? $latest->addressee_user : $latest->user;
            $discussions->push([
                'id' => $latest->id,
                'entity' => 'user',
                'entity_id' => $correspondent?->id,
                'entity_name' => $correspondent?->firstname . ' ' . $correspondent?->lastname,
                'entity_profile' => $correspondent?->avatar_url ?? getWebURL() . '/assets/img/avatar-' . $correspondent?->gender . '.png',
                'last_message' => $latest->message_content,
                'note_doc_uri' => $latest->doc_uri,
                'latest_is_unread' => $latest?->status->getTranslation('status_name', 'fr') == 'Non lu' ? true : false,
                'latest_at' => timeAgo($latest->created_at),
                'messages' => ResourcesMessage::collection($filtered->sortByDesc('created_at')->values())
            ]);
        }

        // === ORGANIZATION
        $organizations_ids = $user->organizations->pluck('id');
        $orgGroups = Message::whereNotNull('addressee_organization_id')
                                ->where('type_id', $type->id)
                                ->whereIn('addressee_organization_id', $organizations_ids)
                                ->when($status, function ($query) use ($status) {
                                    $query->whereHas('status', function($q) use ($status) {
                                        $q->where('status_id', $status->id);
                                    });
                                })
                                ->with('organization', 'user')
                                ->get()
                                ->groupBy('addressee_organization_id');

        foreach ($orgGroups as $messages) {
            $organization = $messages->first()?->addressee_organization;

            if (!$organization) continue;

            $latest = $messages->sortByDesc('created_at')->first();

            $discussions->push([
                'id' => $latest->id,
                'entity' => 'organization',
                'entity_id' => $organization->id,
                'entity_name' => $organization->org_name,
                'entity_profile' => $organization->cover_url ?? getWebURL() . '/assets/img/banner.png',
                'last_message' => !empty($latest->message_content) ? $latest->message_content : (count($latest->files) > 0 ? 'FILES' : null),
                'note_doc_uri' => $latest->doc_uri,
                'latest_is_unread' => $latest?->status->getTranslation('status_name', 'fr') == 'Non lu' ? true : false,
                'latest_at' => timeAgo($latest->created_at),
                'messages' => ResourcesMessage::collection($filtered->sortByDesc('created_at')->values())
            ]);
        }

        // === CIRCLE
        $circle_ids = $user->circles()->wherePivot('status_id', $accepted_status->id)->pluck('circles.id')->toArray();
        $circleGroups = Message::whereNotNull('addressee_circle_id')
                                    ->where('type_id', $type->id)
                                    ->whereIn('addressee_circle_id', $circle_ids)
                                    ->when($status, function ($query) use ($status) {
                                        $query->whereHas('status', function($q) use ($status) {
                                            $q->where('status_id', $status->id);
                                        });
                                    })
                                    ->with('circle', 'user')
                                    ->get()
                                    ->groupBy('addressee_circle_id');

        foreach ($circleGroups as $messages) {
            $circle = $messages->first()?->addressee_circle;

            if (!$circle) continue;

            $latest = $messages->sortByDesc('created_at')->first();

            $discussions->push([
                'id' => $latest->id,
                'entity' => 'circle',
                'entity_id' => $circle->id,
                'entity_name' => $circle->circle_name,
                'entity_profile' => $circle->cover_url ?? getWebURL() . '/assets/img/banner.png',
                'last_message' => !empty($latest->message_content) ? $latest->message_content : (count($latest->files) > 0 ? 'FILES' : null),
                'note_doc_uri' => $latest->doc_uri,
                'latest_is_unread' => $latest?->status->getTranslation('status_name', 'fr') == 'Non lu' ? true : false,
                'latest_at' => timeAgo($latest->created_at),
                'messages' => ResourcesMessage::collection($messages->sortByDesc('created_at')->values())
            ]);
        }

        // === EVENT
        $event_ids = $user->events()->wherePivot('status_id', $accepted_status->id)->pluck('events.id')->toArray();
        $eventGroups = Message::whereNotNull('event_id')
                                ->where('type_id', $type->id)
                                ->whereIn('event_id', $event_ids)
                                ->when($status, function ($query) use ($status) {
                                    $query->whereHas('status', function($q) use ($status) {
                                        $q->where('status_id', $status->id);
                                    });
                                })
                                ->with('event', 'sender')
                                ->get()
                                ->groupBy('event_id');

        foreach ($eventGroups as $messages) {
            $event = $messages->first()?->event;

            if (!$event) continue;

            $latest = $messages->sortByDesc('created_at')->first();

            $discussions->push([
                'id' => $latest->id,
                'entity' => 'event',
                'entity_id' => $event->id,
                'entity_name' => $event->event_title,
                'entity_profile' => $event->cover_url ?? getWebURL() . '/assets/img/banner.png',
                'last_message' => !empty($latest->message_content) ? $latest->message_content : (count($latest->files) > 0 ? 'FILES' : null),
                'note_doc_uri' => $latest->doc_uri,
                'latest_is_unread' => $latest?->status->getTranslation('status_name', 'fr') == 'Non lu' ? true : false,
                'latest_at' => timeAgo($latest->created_at),
                'messages' => ResourcesMessage::collection($messages->sortByDesc('created_at')->values())
            ]);
        }

        // === Finalisation
        $unread_status = Status::where([['status_name->fr', 'Non lu'], ['group_id', $message_status_group->id]])->first();
        $sorted = $discussions->sortByDesc(function($discussion) use ($unread_status) {
            // Si le message est non lu, le mettre en priorité
            return $discussion['messages']->first()->status_id == $unread_status->id ? 1 : 0;
        })->values();
        $paginated = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $partner = null;

        if ($is_subscribed) {
            $valid_subscription = $user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                    $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse($paginated->items(), __('notifications.find_all_messages_success'), $paginated->lastPage(), $paginated->total(), $partnerResource);
    }

    /**
     * GET: Get selected conversation.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function selectedChat($locale, $type_name, $user_id, $entity, $entity_id)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        $invitation_status_group = Group::where('group_name', 'Etat de l\'invitation')->first();
        $message_type_group = Group::where('group_name', 'Type de message')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        $accepted_status = Status::where([['status_name->fr', 'Acceptée'], ['group_id', $invitation_status_group->id]])->first();
        // Requests
        $users_ids = User::whereHas('roles', function ($query) {
                                $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor');
                            })->pluck('id')->toArray();
        $type = Type::where([['type_name->' . $locale, $type_name], ['group_id', $message_type_group->id]])->first();

        if (is_null($type)) return $this->handleError(__('notifications.find_type_404'));

        $user = User::find($user_id);

        if (is_null($user)) return $this->handleError(__('notifications.find_user_404'));

        $messagesQuery = Message::where('type_id', $type->id)
                                    ->with(['user', 'addressee_user', 'addressee_organization', 
                                    'addressee_circle', 'event', 'type', 'status', 'likes', 'files'])->orderByDesc('created_at');
        $is_subscribed = $user->hasValidSubscription();

        switch ($entity) {
            case 'user':
                $messagesQuery->where(function ($q) use ($user_id, $entity_id) {
                    $q->where('user_id', $user_id)->where('addressee_user_id', $entity_id)
                    ->orWhere('user_id', $entity_id)->where('addressee_user_id', $user_id);
                });
                break;
            case 'organization':
                $organization = Organization::find($entity_id);

                if (is_null($organization)) return $this->handleError(__('notifications.find_organization_404'));

                $isMember = Organization::where('id', $organization->id)->whereHas('users', fn($q) => $q->where('users.id', $user_id))->exists();

                if (!$isMember) {
                    return $this->handleError(__('notifications.find_member_404'));
                }

                $messagesQuery->where('addressee_organization_id', $entity_id);
                break;
            case 'circle':
                $circle = Circle::find($entity_id);

                if (is_null($circle)) return $this->handleError(__('notifications.find_circle_404'));

                $isMember = Circle::where('id', $entity_id)->whereHas('users', fn($q) => $q->where('users.id', $user_id)->wherePivot('status_id', $accepted_status->id))->exists();

                if (!$isMember) {
                    return $this->handleError(__('notifications.find_member_404'));
                }

                $messagesQuery->where('addressee_circle_id', $entity_id);
                break;
            case 'event':
                $event = Event::find($entity_id);

                if (is_null($event)) return $this->handleError(__('notifications.find_event_404'));

                $isMember = Event::where('id', $entity_id)->whereHas('users', fn($q) => $q->where('users.id', $user_id)->wherePivot('status_id', $accepted_status->id))->exists();

                if (!$isMember) {
                    return $this->handleError(__('notifications.find_member_404'));
                }

                $messagesQuery->where('event_id', $entity_id);
                break;
            default:
                return $this->handleError(__('validation.custom.owner.required'));
        }

        $messages = $messagesQuery->paginate(10);
        $count_messages = $messages->total();
        $partner = null;

        if ($is_subscribed) {
            $valid_subscription = $user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                    $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages, $partnerResource);
    }


    /**
     * GET: Display all group members with specific status.
     *
     * @param  string $locale
     * @param  string $status_name
     * @param  int $message_id
     * @return \Illuminate\Http\Response
     */
    public function membersWithMessageStatus($locale, $status_name, $message_id)
    {
        // Group
        $group = Group::where('group_name', 'Etat du message')->first();
        // Status
        $status = Status::where([['status_name->' . $locale, $status_name], ['group_id', $group->id]])->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $users = $message->users()->wherePivot('status_id', $status->id)->orderByDesc('created_at')->paginate(10);
        $count_users = $message->users()->wherePivot('status_id', $status->id)->count();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $count_users);
    }

    /**
     * Like/Unlike message.
     *
     * @param  int $message_id
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function switchLike($message_id, $user_id)
    {
        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        // Check if user liked message
        $like = Like::where('user_id', $user->id)->where('for_message_id', $message_id)->first();

        if ($like) {
            $like->delete();

            return $this->handleResponse(new ResourcesMessage($message), __('notifications.delete_like_success'));

        } else {
            Like::create([
                'user_id' => $user->id,
                'for_message_id' => $message->id,
            ]);

            return $this->handleResponse(new ResourcesMessage($message), __('notifications.create_like_success'));
        }
    }

    /**
     * Delete message for a specific user.
     *
     * @param  int $user_id
     * @param  int $message_id
     * @param  string $entity
     * @return \Illuminate\Http\Response
     */
    public function deleteForMyself($user_id, $message_id)
    {
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        $deleted_message_status = Status::where([['status_name->fr', 'Supprimé'], ['group_id', $message_status_group->id]])->first();
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $message->users()->updateExistingPivot($user->id, ['status_id' => $deleted_message_status->id]);

        return $this->handleResponse(new ResourcesMessage($message), __('notifications.find_message_success'));
    }

    /**
     * Delete message for everybody.
     *
     * @param  int $message_id
     * @return \Illuminate\Http\Response
     */
    public function deleteForEverybody($message_id)
    {
        $message = Message::find($message_id);

        if (is_null($message)) {
            return $this->handleError(__('notifications.find_message_404'));
        }

        $message->update([
            'message_content' => __('notifications.delete_message_success'),
            'updated_at' => now()
        ]);
    }

    /**
     * GET: Mark all received messages as read.
     *
     * @param  string $locale
     * @param  string $type_name
     * @param  int $sender_id
     * @param  int $addressee_user_id
     * @return \Illuminate\Http\Response
     */
    public function markAllReadUser($locale, $type_name, $sender_id, $addressee_user_id)
    {
        // Group
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Requests
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $sender = User::find($sender_id);

        if (is_null($sender)) {
            return $this->handleError(__('notifications.find_sender_404'));
        }

        $addressee = User::find($addressee_user_id);

        if (is_null($addressee)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        $all_messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->get();
        $messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->orderByDesc('created_at')->paginate(10);
        $count_messages = Message::where([['type_id', $type->id], ['user_id', $sender->id], ['addressee_user_id', $addressee->id]])->count();

        foreach ($all_messages as $message) {
            $message->update([
                'status_id' => $read_message_status->id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
    }

    /**
     * GET: Mark all organization/circle messages as read.
     *
     * @param  int $user_id
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function markAllReadGroup($user_id, $entity, $entity_id)
    {
        // Group
        $message_status_group = Group::where('group_name', 'Etat du message')->first();
        // Status
        $read_message_status = Status::where([['status_name->fr', 'Lu'], ['group_id', $message_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $all_messages = Message::where('addressee_organization_id', $organization->id)->get();
            $messages = Message::where('addressee_organization_id', $organization->id)->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where('addressee_organization_id', $organization->id)->count();

            foreach ($all_messages as $message) {
                $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $all_messages = Message::where('addressee_circle_id', $circle->id)->get();
            $messages = Message::where('addressee_circle_id', $circle->id)->orderByDesc('created_at')->paginate(10);
            $count_messages = Message::where('addressee_circle_id', $circle->id)->count();

            foreach ($all_messages as $message) {
                $message->users()->updateExistingPivot($user->id, ['status_id' => $read_message_status->id]);
            }

            return $this->handleResponse(ResourcesMessage::collection($messages), __('notifications.find_all_messages_success'), $messages->lastPage(), $count_messages);
        }
    }
}
