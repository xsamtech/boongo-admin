<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Like;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Status;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Http\Request;
use App\Http\Resources\Like as ResourcesLike;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class LikeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $likes = Like::orderByDesc('created_at')->paginate(10);
        $count_likes = Like::count();

        return $this->handleResponse(ResourcesLike::collection($likes), __('notifications.find_all_likes_success'), $likes->lastPage(), $count_likes);
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
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Statuses
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $liked_work_type = Type::where([['type_name->fr', 'Œuvre aimée'], ['group_id', $notification_type_group->id]])->first();
        $liked_message_type = Type::where([['type_name->fr', 'Message aimé'], ['group_id', $notification_type_group->id]])->first();
        $inputs = [
            'user_id' => $request->user_id,
            'for_work_id' => $request->for_work_id,
            'for_message_id' => $request->for_message_id
        ];

        if ($inputs['user_id'] == null) {
            return $this->handleError($inputs['user_id'], __('validation.custom.user.required'), 400);
        }

        $like = Like::create($inputs);

        if ($inputs['for_work_id'] != null) {
            $work = Work::find($inputs['for_work_id']);

            if (is_null($work)) {
                return $this->handleError(__('notifications.find_work_404'));
            }

            Notification::create([
                'type_id' => $liked_work_type->id,
                'status_id' => $unread_notification_status->id,
                'from_user_id' => $like->user_id,
                'to_user_id' => !empty($work->user_id) ? $work->user_id : (!empty($work->organization_id) ? $work->organization->user_id : null),
                'work_id' => $inputs['for_work_id'],
                'like_id' => $like->id
            ]);
        }

        // if ($inputs['for_message_id'] != null) {
        //     $message = Message::find($inputs['for_message_id']);

        //     if (is_null($message)) {
        //         return $this->handleError(__('notifications.find_message_404'));
        //     }

        //     Notification::create([
        //         'type_id' => $liked_message_type->id,
        //         'status_id' => $unread_notification_status->id,
        //         'from_user_id' => $like->user_id,
        //         'to_user_id' => $message->user_id,
        //         'like_id' => $like->id
        //     ]);
        // }

        return $this->handleResponse(new ResourcesLike($like), __('notifications.create_like_success'));
    }

    /**
     * Display the specified resource.
     * 
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $like = Like::find($id);

        if (is_null($like)) {
            return $this->handleError(__('notifications.find_like_404'));
        }

        return $this->handleResponse(new ResourcesLike($like), __('notifications.find_like_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Like $like)
    {
        // Get inputs
        $inputs = [
            'user_id' => $request->user_id,
            'for_work_id' => $request->for_work_id,
            'for_message_id' => $request->for_message_id
        ];

        if ($inputs['user_id'] != null) {
            $like->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['for_work_id'] != null) {
            $like->update([
                'for_work_id' => $inputs['for_work_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['for_message_id'] != null) {
            $like->update([
                'for_message_id' => $inputs['for_message_id'],
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesLike($like), __('notifications.update_like_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Like  $like
     * @return \Illuminate\Http\Response
     */
    public function destroy(Like $like)
    {
        $notifications = Notification::where('like_id', $like->id)->get();

        if ($notifications != null) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }

        $like->delete();

        $likes = Like::all();

        return $this->handleResponse(ResourcesLike::collection($likes), __('notifications.delete_like_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Unlike a work or message.
     *
     * @param  int $user_id
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function unlikeEntity($user_id, $entity, $entity_id)
    {
        $like = null;
        $likes = null;

        if ($entity == 'work') {
            $like = Like::where('user_id', $user_id)->where('for_work_id', $entity_id)->first();
            $likes = Like::where('for_work_id', $entity_id)->get();

            if (is_null($like)) {
                return $this->handleError(__('notifications.find_like_404'));
            }

            $like->delete();

            $notification = Notification::where('from_user_id', $user_id)->where('work_id', $entity_id)->where('like_id', $like->id)->first();

            if (!empty($notification)) {
                $notification->delete();
            }
        }

        if ($entity == 'message') {
            $like = Like::where('user_id', $user_id)->where('for_message_id', $entity_id)->first();
            $likes = Like::where('for_message_id', $entity_id)->get();

            if (is_null($like)) {
                return $this->handleError(__('notifications.find_like_404'));
            }

            $like->delete();
        }

        return $this->handleResponse(ResourcesLike::collection($likes), __('notifications.delete_like_success'));
    }
}
