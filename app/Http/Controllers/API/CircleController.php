<?php

namespace App\Http\Controllers\API;

use App\Models\Circle;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Circle as ResourcesCircle;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CircleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $circles = Circle::orderByDesc('created_at')->paginate(10);
        $count_circles = Circle::count();

        return $this->handleResponse(ResourcesCircle::collection($circles), __('notifications.find_all_circles_success'), $circles->lastPage(), $count_circles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = [
            'circle_name' => $request->circle_name,
            'type_id' => $request->type_id
        ];

        $circle = Circle::create($inputs);

        if ($request->image_64 != null) {
            if ($request->image_type_id == null) {
                return $this->handleError($request->image_type_id, __('validation.required'), 400);
            }

            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/circles/' . $circle->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $circle->update([
                'profile_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesCircle($circle), __('notifications.create_circle_success'));
    }

    /**
     * Display the specified resource.

     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $circle = Circle::find($id);

        if (is_null($circle)) {
            return $this->handleError(__('notifications.find_circle_404'));
        }

        return $this->handleResponse(new ResourcesCircle($circle), __('notifications.find_circle_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Circle $circle)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'circle_name' => $request->circle_name,
            'type_id' => $request->type_id
        ];

        if ($inputs['circle_name'] != null) {
            $circle->update([
                'circle_name' => $request->circle_name,
                'updated_at' => now(),
            ]);
        }

        if ($request->image_64 != null) {
            if ($request->image_type_id == null) {
                return $this->handleError($request->image_type_id, __('validation.required'), 400);
            }

            // $extension = explode('/', explode(':', substr($request->image_64, 0, strpos($request->image_64, ';')))[1])[1];
            $replace = substr($request->image_64, 0, strpos($request->image_64, ',') + 1);
            // Find substring from replace here eg: data:image/png;base64,
            $image = str_replace($replace, '', $request->image_64);
            $image = str_replace(' ', '+', $image);
            // Create image URL
            $image_url = 'images/circles/' . $circle->id . '/' . Str::random(50) . '.png';

            // Upload image
            Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

            $circle->update([
                'profile_url' => $image_url,
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $circle->update([
                'type_id' => $request->type_id,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCircle($circle), __('notifications.update_circle_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Circle $circle)
    {
        $notifications = Notification::where('circle_id', $circle->id)->get();

        if ($notifications != null) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }

        $circle->delete();

        $circles = Circle::all();

        return $this->handleResponse(ResourcesCircle::collection($circles), __('notifications.delete_circle_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search (by filtering or not) an organization
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Circle::query();

        // Apply the filter to the circle name
        $query->where('circle_name', 'LIKE', '%' . $request->data . '%');

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $query->when($request->user_id, function ($query) use ($request) {
            return $query->whereHas('users', function ($query) use ($request) {
                        $query->where('circle_user.user_id', $request->user_id);
                    });
        });

        // Retrieves the query results
        $circles = $query->orderByDesc('updated_at')->paginate(10);
        $count_circles = $query->count();
        $message = ($count_circles > 0 ? __('notifications.find_all_circles_success') : __('notifications.find_circle_404'));

        return $this->handleResponse(ResourcesCircle::collection($circles), $message, $circles->lastPage(), $count_circles);
    }
}
