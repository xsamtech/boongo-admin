<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Notification as ResourcesNotification;
use App\Models\Circle;
use App\Models\Event;
use App\Models\File;
use App\Models\Group;
use App\Models\Like;
use App\Models\ReadNotification;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Notification::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'work_id' => $request->work_id,
            'like_id' => $request->like_id,
            'event_id' => $request->event_id,
            'circle_id' => $request->circle_id
        ];

        $validator = Validator::make($inputs, [
            'type_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $notification = Notification::create($inputs);

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.create_notification_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::find($id);

        if (is_null($notification)) {
            return $this->handleError(__('notifications.find_notification_404'));
        }

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.find_notification_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'work_id' => $request->work_id,
            'like_id' => $request->like_id,
            'event_id' => $request->event_id,
            'circle_id' => $request->circle_id,
            'updated_at' => now()
        ];

        $validator = Validator::make($inputs, [
            'type_id' => ['required'],
            'status_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $notification->update($inputs);

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.update_notification_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        $notifications = Notification::all();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.delete_notification_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all user notifications.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function selectByUser($user_id)
    {
        $notifications = Notification::where('to_user_id', $user_id)->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }

    /**
     * Select all read notifications for a user.
     *
     * @param  $user_id
     * @param  $status_id
     * @return \Illuminate\Http\Response
     */
    public function selectByStatusUser($status_id, $user_id)
    {
        $notifications = Notification::where([['status_id', $status_id], ['to_user_id', $user_id]])->orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }

    /**
     * Change notification status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $ids
     * @param  int $status_id
     * @return \Illuminate\Http\Response
     */
    public function switchStatus(Request $request, $ids, $status_id)
    {
        // Groups
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Type
        $img_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        // Status
        $read_status = Status::where([['status_name->fr', 'Lue'], ['group_id', $notification_status_group->id]])->first();
        // Request
        $status = Status::find($status_id);

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        // 🧠 Découper les IDs depuis l'URL
        $idArray = explode(',', $ids);
        // 🔍 Charger toutes les notifications du groupe
        $notifications = Notification::whereIn('id', $idArray)->get();

        if ($notifications->isEmpty()) {
            return $this->handleError(__('notifications.find_404'));
        }

        // update "status_id" column
        Notification::whereIn('id', $idArray)->update([
            'status_id' => $status->id,
            'updated_at' => now()
        ]);

        // 🧠 On va utiliser la dernière (ou première) comme "représentant"
        $notification = $notifications->last(); // ou ->first() si tu préfères
        // === Déduire l'entité
        $entity = null;
        $entity_id = null;
        $icon = null;
        $image_url = null;

        if (!empty($notification->work_id) AND empty($notification->like_id)) {
            $entity = 'work';
            $work = Work::find($notification->work_id);

            if (!$work) return $this->handleError(__('notifications.find_work_404'));

            $photo = File::where([['type_id', $img_type->id],['work_id', $work->id]])
                            ->get()->first(fn ($img) => isPhotoFile($img->file_url));

            $entity_id = $work->id;
            $icon = 'fa-solid fa-book';
            $image_url = $photo ? $photo->file_url : getWebURL() . '/assets/img/cover.png';

        } else if (!empty($notification->like_id)) {
            $entity = 'like';
            $like = Like::find($notification->like_id);

            if (!$like) return $this->handleError(__('notifications.find_like_404'));

            $work = Work::find($like->for_work_id);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            $photo = File::where([['type_id', $img_type->id], ['work_id', $work->id]])
                            ->get()->first(fn ($img) => isPhotoFile($img->file_url));

            $entity_id = $work->id;
            $icon = 'fa-solid fa-heart';
            $image_url = $photo ? $photo->file_url : getWebURL() . '/assets/img/cover.png';

        } else if (!empty($notification->event_id)) {
            $entity = 'event';
            $event = Event::find($notification->event_id);

            if (!$event) return $this->handleError(__('notifications.find_event_404'));

            $entity_id = $event->id;
            $icon = 'fa-solid fa-calendar';
            $image_url = $event->cover_url ?? getWebURL() . '/assets/img/banner-event.png';

        } else if (!empty($notification->circle_id)) {
            $entity = 'circle';
            $circle = Circle::find($notification->circle_id);

            if (!$circle) return $this->handleError(__('notifications.find_circle_404'));

            $entity_id = $circle->id;
            $icon = 'fa-solid fa-users';
            $image_url = $circle->profile_url ?? getWebURL() . '/assets/img/banner-circle.png';

        } else {
            $entity = 'about';
            $user = User::find($notification->to_user_id);

            if (!$user) return $this->handleError(__('notifications.find_user_404'));

            $entity_id = $user->id;
            $icon = 'fa-solid fa-user';
        }

        // If notification is marked as read, create "ReadNotification" object
        if ($status->id == $read_status->id) {
            ReadNotification::create([
                'text_content' => $request->text_content,
                'redirect_url' => $request->redirect_url,
                'screen' => $request->screen,
                'entity' => $entity,
                'entity_id' => $entity_id,
                'icon' => $icon,
                'image_url' => $image_url,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
                'notification_id' => $notification->id, // 🧠 Le représentant
                'from_user_id' => $notification->from_user_id,
                'to_user_id' => $notification->to_user_id
            ]);

        // Otherwise, find existing "ReadNotification" objects, and delete them
        } else {
            ReadNotification::whereIn('notification_id', $idArray)->delete();
        }

        return $this->handleResponse(new ResourcesNotification($notification), __('notifications.update_status_success'));
    }

    /**
     * Change notification status.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function markAllRead($user_id)
    {
        $status_read = Status::where('status_name', 'Lue')->first();
        $notifications = Notification::where('to_user_id', $user_id)->get();

        // update "status_id" column for all user notifications
        foreach ($notifications as $notification):
            $notification->update([
                'status_id' => $status_read->id,
                'updated_at' => now()
            ]);
        endforeach;

        return $this->handleResponse(ResourcesNotification::collection($notifications), __('notifications.find_all_notifications_success'));
    }
}
