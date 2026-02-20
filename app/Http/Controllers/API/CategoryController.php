<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Resources\Category as ResourcesCategory;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return $this->handleResponse(ResourcesCategory::collection($categories), __('notifications.find_all_categories_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get inputs
        $inputs = [
            'category_name' => [
                'en' => $request->category_name_en,
                'fr' => $request->category_name_fr,
                'ln' => $request->category_name_ln
            ],
            'category_description' => $request->category_description,
            'icon' => $request->icon,
            'color' => $request->color,
            'group_id' => $request->group_id
        ];
        // Select all categories belonging to a group to check unique constraint
        $categories = Category::all();

        // Validate required fields
        if ($inputs['category_name'] == null) {
            return $this->handleError($inputs['category_name'], __('validation.required'), 400);
        }

        // Check if category name already exists
        foreach ($categories as $another_category):
            if ($another_category->category_name == $inputs['category_name']) {
                return $this->handleError($inputs['category_name'], __('validation.custom.category_name.exists'), 400);
            }
        endforeach;

        $category = Category::create($inputs);

        return $this->handleResponse(new ResourcesCategory($category), __('notifications.create_category_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->handleError(__('notifications.find_category_404'));
        }

        return $this->handleResponse(new ResourcesCategory($category), __('notifications.find_category_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'category_name' => [
                'en' => $request->category_name_en,
                'fr' => $request->category_name_fr,
                'ln' => $request->category_name_ln
            ],
            'category_description' => $request->category_description,
            'icon' => $request->icon,
            'color' => $request->color,
            'group_id' => $request->group_id
        ];
        // Select all categories and specific category to check unique constraint
        $categories = Category::all();
        $current_category = Category::find($inputs['id']);

        if ($inputs['category_name'] != null) {
            foreach ($categories as $another_category):
                if ($current_category->category_name != $inputs['category_name']) {
                    if ($another_category->category_name == $inputs['category_name']) {
                        return $this->handleError($inputs['category_name'], __('validation.custom.category_name.exists'), 400);
                    }
                }
            endforeach;

            $category->update([
                'category_name' => [
                    'en' => $request->category_name_en,
                    'fr' => $request->category_name_fr,
                    'ln' => $request->category_name_ln
                ],
                'updated_at' => now()
            ]);
        }

        if ($inputs['category_description'] != null) {
            $category->update([
                'category_description' => $request->category_description,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['icon'] != null) {
            $category->update([
                'icon' => $request->icon,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['color'] != null) {
            $category->update([
                'color' => $request->color,
                'updated_at' => now(),
            ]);
        }

        if ($inputs['group_id'] != null) {
            $category->update([
                'group_id' => $request->group_id,
                'updated_at' => now(),
            ]);
        }

        return $this->handleResponse(new ResourcesCategory($category), __('notifications.update_category_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        $categories = Category::all();

        return $this->handleResponse(ResourcesCategory::collection($categories), __('notifications.delete_category_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Search a category by its real name.
     *
     * @param  string $locale
     * @param  string $data
     * @return \Illuminate\Http\Response
     */
    public function search($locale, $data)
    {
        $category = Category::where('category_name->' . $locale, $data)->first();

        if (is_null($category)) {
            return $this->handleError(__('notifications.find_category_404'));
        }

        return $this->handleResponse(new ResourcesCategory($category), __('notifications.find_category_success'));
    }

    /**
     * Find all type by group.
     *
     * @param  string $group_name
     * @return \Illuminate\Http\Response
     */
    public function findByGroup($group_name)
    {
        $group = Group::where('group_name', $group_name)->first();

        if (is_null($group)) {
            return $this->handleError(__('notifications.find_group_404'));
        }

        $categories = Category::where('group_id', $group->id)->get();

        return $this->handleResponse(ResourcesCategory::collection($categories), __('notifications.find_all_categories_success'));
    }

    /**
     * Find all categories used in works.
     *
     * @return \Illuminate\Http\Response
     */
    public function allUsedInWorks()
    {
        $categories = Category::whereHas('works')->get();

        return $this->handleResponse(ResourcesCategory::collection($categories), __('notifications.find_all_categories_success'));
    }

    /**
     * Find all categories used in works type, .
     *
     * @param  int  $type_id
     * @return \Illuminate\Http\Response
     */
    public function allUsedInWorksType($type_id)
    {
        $categories = Category::whereHas('works', function ($query) use ($type_id) {
                                    $query->where('type_id', $type_id);
                                })->get();

        return $this->handleResponse(ResourcesCategory::collection($categories), __('notifications.find_all_categories_success'));
    }
}
