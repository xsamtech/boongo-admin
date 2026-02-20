<?php

namespace App\Http\Controllers\API;

use App\Models\CourseYear;
use Illuminate\Http\Request;
use App\Http\Resources\CourseYear as ResourcesCourseYear;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CourseYearController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $course_years = CourseYear::orderByDesc('created_at')->paginate(10);
        $count_course_years = CourseYear::count();

        return $this->handleResponse(ResourcesCourseYear::collection($course_years), __('notifications.find_all_course_years_success'), $course_years->lastPage(), $count_course_years);
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
            'year' => $request->year,
        ];
        $course_years = CourseYear::all();

        foreach ($course_years as $another_year):
            if ($another_year->year == $inputs['year']) {
                return $this->handleError($inputs['year'], __('validation.custom.course_year.exists'), 400);
            }
        endforeach;

        $course_year = CourseYear::create($inputs);

        return $this->handleResponse(new ResourcesCourseYear($course_year), __('notifications.create_course_year_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course_year = CourseYear::find($id);

        if (is_null($course_year)) {
            return $this->handleError(__('notifications.find_course_year_404'));
        }

        return $this->handleResponse(new ResourcesCourseYear($course_year), __('notifications.find_course_year_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CourseYear  $course_year
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CourseYear $course_year)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'year' => $request->year,
        ];

        if (trim($inputs['year']) != null) {
            // Select all years and current year to check unique constraint
            $course_years = CourseYear::all();
            $current_course_year = CourseYear::find($inputs['id']);

            foreach ($course_years as $another_year):
                if ($current_course_year->year != $inputs['year']) {
                    if ($another_year->year == $inputs['year']) {
                        return $this->handleError($inputs['year'], __('validation.custom.course_year.exists'), 400);
                    }
                }
            endforeach;

            $course_year->update([
                'year' => $inputs['year'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesCourseYear($course_year), __('notifications.update_course_year_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CourseYear  $course_year
     * @return \Illuminate\Http\Response
     */
    public function destroy(CourseYear $course_year)
    {
        $course_year->delete();

        $course_years = CourseYear::orderBy('created_at')->paginate(10);
        $count_course_years = CourseYear::count();

        return $this->handleResponse(ResourcesCourseYear::collection($course_years), __('notifications.delete_course_year_success'), $course_years->lastPage(), $count_course_years);
    }
}
