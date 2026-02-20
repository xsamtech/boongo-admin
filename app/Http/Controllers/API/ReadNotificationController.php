<?php

namespace App\Http\Controllers\API;

use App\Models\ReadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReadNotification as ResourcesReadNotification;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ReadNotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $read_notifications = ReadNotification::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.find_all_notifications_success'));
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
            'text_content' => $request->text_content,
            'redirect_url' => $request->redirect_url,
            'screen' => $request->screen,
            'entity' => $request->entity,
            'entity_id' => $request->entity_id,
            'icon' => $request->icon,
            'image_url' => $request->image_url,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'notification_id' => $request->notification_id,
            'user_id' => $request->user_id,
        ];

        $validator = Validator::make($inputs, [
            'notification_id' => ['required'],
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $read_notification = ReadNotification::create($inputs);

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.create_notification_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $read_notification = ReadNotification::find($id);

        if (is_null($read_notification)) {
            return $this->handleError(__('notifications.find_notification_404'));
        }

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.find_notification_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReadNotification  $read_notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReadNotification $read_notification)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'text_content' => $request->text_content,
            'redirect_url' => $request->redirect_url,
            'screen' => $request->screen,
            'entity' => $request->entity,
            'entity_id' => $request->entity_id,
            'icon' => $request->icon,
            'image_url' => $request->image_url,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'notification_id' => $request->notification_id,
            'user_id' => $request->user_id,
        ];

        $validator = Validator::make($inputs, [
            'notification_id' => ['required'],
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $read_notification->update($inputs);

        return $this->handleResponse(new ResourcesReadNotification($read_notification), __('notifications.update_notification_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReadNotification  $read_notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReadNotification $read_notification)
    {
        $read_notification->delete();

        $read_notifications = ReadNotification::all();

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.delete_notification_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Select all user read notifications.
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public function selectByUser($user_id)
    {
        $read_notifications = ReadNotification::where('to_user_id', $user_id)->orderByDesc('created_at')->paginate(10);

        return $this->handleResponse(ResourcesReadNotification::collection($read_notifications), __('notifications.find_all_notifications_success'), $read_notifications->lastPage(), $read_notifications->total());
    }
}
