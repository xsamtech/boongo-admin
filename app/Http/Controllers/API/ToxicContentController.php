<?php

namespace App\Http\Controllers\API;

use App\Models\Message;
use App\Models\ReportReason;
use App\Models\ToxicContent;
use App\Models\Work;
use Illuminate\Http\Request;
use App\Http\Resources\ToxicContent as ResourcesToxicContent;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ToxicContentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $toxic_contents = ToxicContent::all();

        return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.find_all_toxic_contents_success'));
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
            'is_reported' => isset($request->is_reportedt) ? $request->is_reported : 1,
            'for_user_id' => $request->for_user_id,
            'for_work_id' => $request->for_work_id,
            'for_message_id' => $request->for_message_id,
            'explanation' => $request->explanation,
            'is_unlocked' => isset($request->is_unlocked) ? $request->is_unlocked : 1,
            'is_archived' => isset($request->is_archived) ? $request->is_archived : 0,
            'report_reason_id' => $request->report_reason_id,
            'user_id' => $request->user_id,
        ];

        // Validate required fields
        if ($inputs['for_user_id'] == null && $inputs['for_work_id'] == null && $inputs['for_message_id'] == null) {
            return $this->handleError(__('validation.custom.owner.required'));
        }

        if ($inputs['user_id'] == null) {
            return $this->handleError($inputs['user_id'], __('validation.required'), 400);
        }

        $toxic_content = ToxicContent::create($inputs);

        if ($toxic_content->for_user_id == null) {
            if ($toxic_content->for_work_id != null) {
                $work = Work::find($toxic_content->for_work_id);

                if ($work->user_id != null) {
                    $toxic_content->update([
                        'for_user_id' => $work->user_id,
                        'updated_at' => now(),
                    ]);
                }

                // Check if user must be blocked
                $report_reason = ReportReason::find($toxic_content->report_reason_id);
                $count_toxic_contents_with_reason = ToxicContent::where([['for_user_id', $work->user_id], ['report_reason_id', $report_reason->id], ['is_archived', 0]])->count();

                if ($count_toxic_contents_with_reason >= $report_reason->blocked_for) {
                    $toxic_content->update([
                        'is_unlocked' => 0,
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($toxic_content->for_message_id != null) {
                $message = Message::find($toxic_content->for_message_id);

                if ($message->user_id != null) {
                    $toxic_content->update([
                        'for_user_id' => $message->user_id,
                        'updated_at' => now(),
                    ]);
                }

                // Check if user must be blocked
                $report_reason = ReportReason::find($toxic_content->report_reason_id);
                $count_toxic_contents_with_reason = ToxicContent::where([['for_user_id', $message->user_id], ['report_reason_id', $report_reason->id], ['is_archived', 0]])->count();

                if ($count_toxic_contents_with_reason >= $report_reason->blocked_for) {
                    $toxic_content->update([
                        'is_unlocked' => 0,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return $this->handleResponse(new ResourcesToxicContent($toxic_content), __('notifications.create_toxic_content_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $toxic_content = ToxicContent::find($id);

        if (is_null($toxic_content)) {
            return $this->handleError(__('notifications.find_toxic_content_404'));
        }

        return $this->handleResponse(new ResourcesToxicContent($toxic_content), __('notifications.find_toxic_content_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ToxicContent  $toxic_content
     */
    public function update(Request $request, ToxicContent $toxic_content)
    {
        // Get inputs
        $inputs = [
            'is_reported' => $request->is_reported,
            'for_user_id' => $request->for_user_id,
            'for_work_id' => $request->for_work_id,
            'for_message_id' => $request->for_message_id,
            'explanation' => $request->explanation,
            'is_unlocked' => $request->is_unlocked,
            'is_archived' => $request->is_archived,
            'report_reason_id' => $request->report_reason_id,
            'user_id' => $request->user_id,
        ];

        if ($inputs['is_reported'] != null) {
            $toxic_content->update([
                'is_reported' => $request->is_reported,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['for_user_id'] != null) {
            $toxic_content->update([
                'for_user_id' => $request->for_user_id,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['for_work_id'] != null) {
            $toxic_content->update([
                'for_work_id' => $request->for_work_id,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['for_message_id'] != null) {
            $toxic_content->update([
                'for_message_id' => $request->for_message_id,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['explanation'] != null) {
            $toxic_content->update([
                'explanation' => $request->explanation,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_unlocked'] != null) {
            $toxic_content->update([
                'is_unlocked' => $request->is_unlocked,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_archived'] != null) {
            $toxic_content->update([
                'is_archived' => $request->is_archived,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['report_reason_id'] != null) {
            $toxic_content->update([
                'report_reason_id' => $request->report_reason_id,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $toxic_content->update([
                'user_id' => $request->user_id,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesToxicContent($toxic_content), __('notifications.update_toxic_content_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportReason  $report_reason
     * @return \Illuminate\Http\Response
     */
    public function destroy(ToxicContent $toxic_content)
    {
        $toxic_content->delete();

        $toxic_contents = ToxicContent::all();

        return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.delete_toxic_content_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find all type by entity.
     *
     * @param  int $is_reported
     * @param  int $is_unlocked
     * @return \Illuminate\Http\Response
     */
    public function findByIsReported($is_reported, $is_unlocked)
    {
        $toxic_contents = ToxicContent::where([['is_reported', $is_reported], ['is_unlocked', $is_unlocked]])->get();

        return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.find_all_toxic_contents_success'));
    }

    /**
     * Find all blocked entity by specific user.
     *
     * @param  int $user_id
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function findBlockedEntity($user_id, $entity, $entity_id)
    {
        if ($entity == 'user') {
            $toxic_contents = ToxicContent::where([['user_id', $user_id], ['for_user_id', $entity_id], ['is_unlocked', 0], ['is_reported', 0]])->get();

            return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.find_all_toxic_contents_success'));
        }

        if ($entity == 'work') {
            $toxic_contents = ToxicContent::where([['user_id', $user_id], ['for_work_id', $entity_id], ['is_unlocked', 0], ['is_reported', 0]])->get();

            return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.find_all_toxic_contents_success'));
        }

        if ($entity == 'message') {
            $toxic_contents = ToxicContent::where([['user_id', $user_id], ['for_message_id', $entity_id], ['is_unlocked', 0], ['is_reported', 0]])->get();

            return $this->handleResponse(ResourcesToxicContent::collection($toxic_contents), __('notifications.find_all_toxic_contents_success'));
        }
    }

    /**
     * Unlock user.
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function unlockUser($user_id)
    {
        $toxic_content = ToxicContent::where([['for_user_id', $user_id], ['is_unlocked', 0]])->first();

        if (is_null($toxic_content)) {
            return $this->handleError(__('notifications.find_toxic_content_404'));
        }

        $report_reason = ReportReason::find($toxic_content->report_reason_id);

        if (is_null($report_reason)) {
            return $this->handleError(__('notifications.find_report_reason_404'));
        }

        $remainingDays = $toxic_content->remaining_days;

        if ($remainingDays >= $report_reason->blocked_for) {
            $toxic_content->update([
                'is_unlocked' => 1,
                'updated_at' => now(),
            ]);
        }

        // After unblocking the user, we archive all the places where he was reported
        $toxic_contents = ToxicContent::where([['for_user_id', $user_id], ['report_reason_id', $report_reason->id]])->get();

        foreach ($toxic_contents as $content) {
            $content->update([
                'is_archived' => 1,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesToxicContent($toxic_content), __('notifications.find_toxic_content_success'));
    }
}
