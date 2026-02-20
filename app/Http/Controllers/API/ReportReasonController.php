<?php

namespace App\Http\Controllers\API;

use App\Models\ReportReason;
use Illuminate\Http\Request;
use App\Http\Resources\ReportReason as ResourcesReportReason;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ReportReasonController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $report_reasons = ReportReason::all();

        return $this->handleResponse(ResourcesReportReason::collection($report_reasons), __('notifications.find_all_report_reasons_success'));
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
            'reason_content' => [
                'en' => $request->reason_content_en,
                'fr' => $request->reason_content_fr,
                'ln' => $request->reason_content_ln
            ],
            'reports_count' => $request->reports_count,
            'blocked_for' => $request->blocked_for,
            'entity' => !empty($request->entity) ? $request->entity : 'user'
        ];
        // Select all report_reasons to check unique constraint
        $report_reasons = ReportReason::all();

        // Validate required fields
        if ($inputs['reason_content'] == null) {
            return $this->handleError($inputs['reason_content'], __('validation.required'), 400);
        }

        if ($inputs['reports_count'] == null) {
            return $this->handleError($inputs['reports_count'], __('validation.required'), 400);
        }

        if ($inputs['blocked_for'] == null) {
            return $this->handleError($inputs['blocked_for'], __('validation.required'), 400);
        }

        // Check if reason content already exists
        foreach ($report_reasons as $another_reason):
            if ($another_reason->reason_content == $inputs['reason_content']) {
                return $this->handleError($inputs['reason_content'], __('validation.custom.content.exists'), 400);
            }
        endforeach;

        $report_reason = ReportReason::create($inputs);

        return $this->handleResponse(new ResourcesReportReason($report_reason), __('notifications.create_report_reason_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $report_reason = ReportReason::find($id);

        if (is_null($report_reason)) {
            return $this->handleError(__('notifications.find_report_reason_404'));
        }

        return $this->handleResponse(new ResourcesReportReason($report_reason), __('notifications.find_report_reason_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReportReason  $report_reason
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReportReason $report_reason)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'reason_content' => [
                'en' => $request->reason_content_en,
                'fr' => $request->reason_content_fr,
                'ln' => $request->reason_content_ln
            ],
            'reports_count' => $request->reports_count,
            'blocked_for' => $request->blocked_for,
            'entity' => $request->entity
        ];
        // Select all report reasons and specific report reason to check unique constraint
        $report_reasons = ReportReason::all();
        $current_report_reason = ReportReason::find($inputs['id']);

        if ($inputs['reason_content'] != null) {
            foreach ($report_reasons as $another_reason):
                if ($current_report_reason->reason_content != $inputs['reason_content']) {
                    if ($another_reason->reason_content == $inputs['reason_content']) {
                        return $this->handleError($inputs['reason_content'], __('validation.custom.content.exists'), 400);
                    }
                }
            endforeach;

            $report_reason->update([
                'reason_content' => [
                    'en' => $request->reason_content_en,
                    'fr' => $request->reason_content_fr,
                    'ln' => $request->reason_content_ln
                ],
                'updated_at' => now()
            ]);
        }

        if ($inputs['reports_count'] != null) {
            $report_reason->update([
                'reports_count' => $request->reports_count,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['blocked_for'] != null) {
            $report_reason->update([
                'blocked_for' => $request->blocked_for,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['entity'] != null) {
            $report_reason->update([
                'entity' => $request->entity,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesReportReason($report_reason), __('notifications.update_report_reason_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReportReason  $report_reason
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReportReason $report_reason)
    {
        $report_reason->delete();

        $report_reasons = ReportReason::all();

        return $this->handleResponse(ResourcesReportReason::collection($report_reasons), __('notifications.delete_report_reason_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find all type by entity.
     *
     * @param  string $entity
     * @return \Illuminate\Http\Response
     */
    public function findByEntity($entity)
    {
        $report_reasons = ReportReason::where('entity', $entity)->get();

        return $this->handleResponse(ResourcesReportReason::collection($report_reasons), __('notifications.delete_report_reason_success'));
    }
}
