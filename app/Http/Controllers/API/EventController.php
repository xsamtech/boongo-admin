<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\Group;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Status;
use App\Models\Type;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Event as ResourcesEvent;
use App\Http\Resources\User as ResourcesUser;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class EventController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::orderByDesc('created_at')->paginate(10);
        $count_events = Event::count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
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
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $event_status_group = Group::where('group_name', 'Etat de l\'événement')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        $access_type_group = Group::where('group_name', 'Type d\'accès')->first();
        // Status
        $created_status = Status::where([['status_name->fr', 'Créé'], ['group_id', $event_status_group->id]])->first();
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Type
        $public_type = Type::where([['type_name->fr', 'Public'], ['group_id', $access_type_group->id]])->first();
        $new_event_type = Type::where([['type_name->fr', 'Nouvel événement'], ['group_id', $notification_type_group->id]])->first();
        $invitation_as_speaker_type = Type::where([['type_name->fr', 'Invitation en tant que speaker'], ['group_id', $notification_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'event_title' => $request->event_title,
            'event_description' => $request->event_description,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'event_place' => $request->event_place,
            'event_place_address' => $request->event_place_address,
            'type_id' => isset($request->type_id) ? $request->type_id : $public_type->id,
            'status_id' => isset($request->status_id) ? $request->status_id : $created_status->id,
            'organization_id' => $request->organization_id
        ];

        if (trim($inputs['event_title']) == null) {
            return $this->handleError(__('miscellaneous.public.event.data.event_title'), __('validation.required'), 400);
        }

        if (trim($inputs['start_at']) == null) {
            return $this->handleError(__('miscellaneous.public.event.data.start_at'), __('validation.required'), 400);
        }

        if (trim($inputs['type_id']) == null) {
            return $this->handleError(__('miscellaneous.public.event.data.access_type'), __('validation.required'), 400);
        }

        $event = Event::create($inputs);

        if (isset($request->image_64)) {
            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_path = 'images/events/' . $event->id . '/cover/' . Str::random(50) . '.png';

            // Upload image
            Storage::disk('public')->put($image_path, base64_decode($image));

            $event->update([
                'cover_url' => Storage::url($image_path),
                'updated_at' => now()
            ]);
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $organization = Organization::find($event->organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        $creator_user_id = $organization->user_id;  // The user who created the event (from "organizations" table)
        $organization_users = $organization->users()->where('organization_id', $event->organization_id)
                                                ->where('user_id', '!=', $creator_user_id)  // Do not send a notification to the person who created the event
                                                ->get();

        if ($organization_users != null) {
            // If the event is public, send the notification to all organization members
            if ($event->type_id == $public_type->id) {
                foreach ($organization_users as $organization_user) {
                    Notification::create([
                        'type_id' => $new_event_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $creator_user_id,
                        'to_user_id' => $organization_user->id,
                        'event_id' => $event->id,
                    ]);
                }
            }
        }

        $event_speakers = $event->users()->wherePivot('is_speaker', 1)->get();

        if ($event_speakers != null) {
            // Invite all speakers of the event
            foreach ($event_speakers as $event_speaker) {
                Notification::create([
                    'type_id' => $invitation_as_speaker_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $creator_user_id,
                    'to_user_id' => $event_speaker->id,
                    'event_id' => $event->id,
                ]);
            }
        }

        return $this->handleResponse(new ResourcesEvent($event), __('notifications.create_event_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $event = Event::find($id);

        if (is_null($event)) {
            return $this->handleError(__('notifications.find_event_404'));
        }

        return $this->handleResponse(new ResourcesEvent($event), __('notifications.find_event_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        // Groups
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Statuses
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $event_title_updated_type = Type::where([['type_name->fr', 'Titre d\'événement modifié'], ['group_id', $notification_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'event_title' => $request->event_title,
            'event_description' => $request->event_description,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'event_place' => $request->event_place,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'organization_id' => $request->organization_id
        ];
        // Select specific event to check unique constraint
        $current_event = Event::find($inputs['id']);

        if (is_null($current_event)) {
            return $this->handleError(__('notifications.find_event_404'));
        }

        if ($inputs['event_title'] != null) {
            if ($current_event->event_title != $inputs['event_title']) {
                if (trim($inputs['event_title']) == null) {
                    return $this->handleError(__('miscellaneous.public.event.data.event_title'), __('validation.required'), 400);
                }
            }

            $event->update([
                'event_title' => $inputs['event_title'],
                'former_event_title' => $current_event->event_title,
                'updated_at' => now()
            ]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            $organization = Organization::find($event->organization_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            $creator_user_id = $organization->user_id;  // The user who created the event (from "organizations" table)
            $organization_users = $organization->users()->where('organization_id', $event->organization_id)
                                                        ->where('user_id', '!=', $creator_user_id)  // Do not send a notification to the person who created the event
                                                        ->get();

            if ($organization_users != null) {
                foreach ($organization_users as $organization_user):
                    Notification::create([
                        'type_id' => $event_title_updated_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $creator_user_id,
                        'to_user_id' => $organization_user->id,
                        'event_id' => $current_event->id
                    ]);
                endforeach;
            }
        }

        if ($inputs['event_description'] != null) {
            $event->update([
                'event_description' => $inputs['event_description'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['start_at'] != null) {
            if ($current_event->start_at != $inputs['start_at']) {
                if (trim($inputs['start_at']) == null) {
                    return $this->handleError(__('miscellaneous.public.event.data.start_at'), __('validation.required'), 400);
                }
            }

            $event->update([
                'start_at' => $inputs['start_at'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['end_at'] != null) {
            $event->update([
                'end_at' => $inputs['end_at'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['event_place'] != null) {
            $event->update([
                'event_place' => $inputs['event_place'],
                'event_place_address' => !empty($request->event_place_address) ? $request->event_place_address : $current_event->event_place_address,
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $event->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['status_id'] != null) {
            $event->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['organization_id'] != null) {
            $event->update([
                'organization_id' => $inputs['organization_id'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesEvent($event), __('notifications.update_event_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $notifications = Notification::where('event_id', $event->id)->get();

        if ($notifications != null) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }

        $event->delete();

        $events = Event::orderByDesc('created_at')->paginate(10);
        $count_events = Event::count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.delete_event_success'), $events->lastPage(), $count_events);
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a event by its title.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Event::query();

        // Filter by event title if "data" is present in the query
        if ($request->has('data')) {
            $query->where('event_title', 'LIKE', '%' . $request->data . '%');
        }

        // Apply filter on dates if "date_from" or "date_to" is set
        if ($request->date_from || $request->date_to) {
            $query->whereBetween('created_at', [
                $request->date_from ?: '1970-01-01', // Default to a very old date if not set
                $request->date_to ?: now(),          // Default to current date if not set
            ]);
        }

        // Add dynamic conditions (type_id)
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        // Retrieve results with pagination
        $events = $query->orderByDesc('created_at')->paginate(10);
        // Count events to determine return message
        $count_events = $events->total(); // Using Pagination to Get Total Events
        $message = $count_events > 0 ? __('notifications.find_all_events_success') : __('notifications.find_event_404');

        return $this->handleResponse(ResourcesEvent::collection($events), $message, $events->lastPage(), $count_events);
    }

    /**
     * Find all events by type.
     *
     * @param  string $locale
     * @param  string $type_name
     * @return \Illuminate\Http\Response
     */
    public function findByType($locale, $type_name)
    {
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        $events = Event::where('type_id', $type->id)->orderByDesc('created_at')->paginate(10);
        $count_events = Event::where('type_id', $type->id)->count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
    }

    /**
     * Find all events by status.
     *
     * @param  string $locale
     * @param  string $status_name
     * @return \Illuminate\Http\Response
     */
    public function findByStatus($locale, $status_name)
    {
        $status = Type::where('status_name->' . $locale, $status_name)->first();

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        $events = Event::where('status_id', $status->id)->orderByDesc('created_at')->paginate(10);
        $count_events = Event::where('status_id', $status->id)->count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
    }

    /**
     * Find all organization events.
     *
     * @param  int $organization_id
     * @return \Illuminate\Http\Response
     */
    public function findByOrganization($organization_id)
    {
        $organization = Organization::find($organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        $events = Event::where('organization_id', $organization->id)->orderByDesc('created_at')->paginate(10);
        $count_events = Event::where('organization_id', $organization->id)->count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
    }

    /**
     * Find all event speakers.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function findSpeakers($id)
    {
        $event = Event::find($id);

        if (is_null($event)) {
            return $this->handleError(__('notifications.find_event_404'));
        }

        $users = $event->users()->wherePivot('is_speaker', 1)->paginate(10);
        $count_users = $event->users()->wherePivot('is_speaker', 1)->count();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $count_users);
    }

    /**
     * Find all organization events filtered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $organization_id
     * @return \Illuminate\Http\Response
     */
    public function filterForOrganization(Request $request, $organization_id)
    {
        $organization = Organization::find($organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        $query = Event::query();

        $query->where('organization_id', $organization->id);

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $query->when($request->status_id, function ($query) use ($request) {
            return $query->where('status_id', $request->status_id);
        });

        $query->when(is_null($request->category_id), function ($query) {
            return $query->whereNull('category_id');
        }, function ($query) use ($request) {
            return $query->where('category_id', $request->category_id);
        });

        // Retrieves the query results
        $events = $query->orderByDesc('created_at')->paginate(10);
        $count_events = $query->count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
    }

    /**
     * Find all events filtered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filterForEverybody(Request $request)
    {
        $query = Event::query();

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $query->when($request->status_id, function ($query) use ($request) {
            return $query->where('status_id', $request->status_id);
        });

        $query->when($request->category_id, function ($query) use ($request) {
            return $query->where('category_id', $request->category_id);
        });

        // Retrieves the query results
        $events = $query->orderByDesc('created_at')->paginate(10);
        $count_events = $query->count();

        return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events);
    }

    /**
     * Update cover picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function updateCover(Request $request, $id)
    {
        $inputs = [
            'event_id' => $request->event_id,
            'image_64' => $request->image_64,
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
        // Create image URL
		$image_path = 'images/events/' . $inputs['event_id'] . '/cover/' . Str::random(50) . '.png';

		// Upload image
        Storage::disk('public')->put($image_path, base64_decode($image));

		$event = Event::find($id);

        $event->update([
            'cover_url' => Storage::url($image_path),
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesEvent($event), __('notifications.update_event_success'));
    }
}
