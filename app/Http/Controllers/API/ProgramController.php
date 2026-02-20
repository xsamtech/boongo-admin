<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Group;
use App\Models\Program;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Program as ResourcesProgram;
use App\Models\CourseYear;
use App\Models\Organization;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class ProgramController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $programs = Program::orderByDesc('created_at')->paginate(10);
        $count_programs = Program::count();

        return $this->handleResponse(ResourcesProgram::collection($programs), __('notifications.find_all_programs_success'), $programs->lastPage(), $count_programs);
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
            'class' => $request->class,
            'course_year_id' => $request->course_year_id,
            'organization_id' => $request->organization_id
        ];

        $program = Program::create($inputs);

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return $this->handleError($request->file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            // Group
            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            // Types
            $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
            $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();

            if ($type->id == $image_type->id AND $type->id == $document_type->id AND $type->id == $audio_type->id) {
                return $this->handleError(__('notifications.type_is_not_file'));
            }

            $custom_path = ($type->id == $document_type->id ? 'documents/organizations/' . $inputs['organization_id'] . '/programs' : ($type->id == $audio_type->id ? 'audios/organizations/' . $inputs['organization_id'] . '/programs' : 'images/organizations/' . $inputs['organization_id'] . '/programs'));
            $file_url =  $custom_path . '/' . $program->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            $dir_result = Storage::disk('s3')->put($file_url, $request->file('file_url'));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : null,
                'file_url' => config('filesystems.disks.s3.url') . $dir_result,
                'type_id' => $request->file_type_id,
                'program_id' => $program->id
            ]);
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.create_program_success'));
    }

    /**
     * Display the specified resource.
     * 
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $program = Program::find($id);

        if (is_null($program)) {
            return $this->handleError(__('notifications.find_program_404'));
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.find_program_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Program $program)
    {
        // Get inputs
        $inputs = [
            'class' => $request->class,
            'course_year_id' => $request->course_year_id,
            'organization_id' => $request->organization_id
        ];

        if ($inputs['class'] != null) {
            $program->update([
                'class' => $inputs['class'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['course_year_id'] != null) {
            $program->update([
                'course_year_id' => $inputs['course_year_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['organization_id'] != null) {
            $program->update([
                'organization_id' => $inputs['organization_id'],
                'updated_at' => now(),
            ]);
        }

        if ($request->hasFile('file_url')) {
            if ($request->file_type_id == null) {
                return $this->handleError($request->file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            // Group
            $file_type_group = Group::where('group_name', 'Type de fichier')->first();
            // Types
            $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
            $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
            $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();

            if ($type->id == $image_type->id AND $type->id == $document_type->id AND $type->id == $audio_type->id) {
                return $this->handleError(__('notifications.type_is_not_file'));
            }

            $custom_path = ($type->id == $document_type->id ? 'documents/organizations/' . $inputs['organization_id'] . '/programs' : ($type->id == $audio_type->id ? 'audios/organizations/' . $inputs['organization_id'] . '/programs' : 'images/organizations/' . $inputs['organization_id'] . '/programs'));
            $file_url =  $custom_path . '/' . $program->id . '/' . Str::random(50) . '.' . $request->file('file_url')->extension();

            // Upload file
            $dir_result = Storage::disk('s3')->put($file_url, $request->file('file_url'));

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : null,
                'file_url' => config('filesystems.disks.s3.url') . $dir_result,
                'type_id' => $request->file_type_id,
                'program_id' => $program->id
            ]);
        }

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.update_program_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Program  $program
     * @return \Illuminate\Http\Response
     */
    public function destroy(Program $program)
    {
        $program->delete();

        $programs = Program::all();

        return $this->handleResponse(ResourcesProgram::collection($programs), __('notifications.delete_program_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Retrieves all programs by course year and organization ID.
     *
     * @param  string  $course_year
     * @param  int  $organization_id
     * @return \Illuminate\Http\Response
     */
    public function findAllByYearAndOrganization($course_year, $organization_id)
    {
        $course_year = CourseYear::where('year', $course_year)->first();

        if (is_null($course_year)) {
            return $this->handleError(__('notifications.find_course_year_404'));
        }

        $organization = Organization::find($organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        // Retrieve programs and count
        $programs = Program::where('course_year_id', $course_year->id)->where('organization_id', $organization->id)->orderByDesc('updated_at')->get();

        return $this->handleResponse(ResourcesProgram::collection($programs), __('notifications.find_all_programs_success'));
    }

    /**
     * Add a program by course year and organization ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organization_id
     * @return \Illuminate\Http\Response
     */
    public function addOrganizationProgram(Request $request, $organization_id)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        // Request
        $organization = Organization::find($organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        $course_year = CourseYear::where('year', $request->course_year)->first();

        if (is_null($course_year)) {
            $course_year = CourseYear::create([
                'year' => $request->course_year,
            ]);
        }

        // Retrieve programs and count
        $program = Program::create([
            'class' => $request->class,
            'course_year_id' => $course_year->id,
            'organization_id' => $organization->id
        ]);

        $file = $request->file('document_url');
        // Types of extensions for different file types
        $document_extensions = ['pdf'];
        // Checking the file extension
        $file_extension = $file->getClientOriginalExtension();
        // File type check
        $custom_uri = '';
        $is_valid_type = false;
        $file_type_id = null;

        if (in_array($file_extension, $document_extensions)) { // File is a document
            $custom_uri = 'documents/organizations/' . $organization->id . '/programs';
            $file_type_id = $document_type->id;
            $is_valid_type = true;
        }

        // If the extension does not match any valid type
        if (!$is_valid_type) {
            return $this->handleError(__('notifications.type_is_not_file'));
        }

        // Generate a unique path for the file
        $filename = $file->getClientOriginalName();
        $cleaned_filename = sanitizeFileName($filename);
        $file_url =  $custom_uri . '/' . $program->id . '/' . $cleaned_filename ;

        // Upload file
        try {
            $file->storeAs($custom_uri . '/' . $program->id, $cleaned_filename, 's3');

        } catch (\Throwable $th) {
            return $this->handleError($th, __('notifications.create_work_file_500'), 500);
        }

        // Creating the database record for the file
        File::create([
            'file_name' => trim($cleaned_filename),
            'file_url' => config('filesystems.disks.s3.url') . $file_url,
            'type_id' => $file_type_id,
            'program_id' => $program->id
        ]);

        return $this->handleResponse(new ResourcesProgram($program), __('notifications.create_program_success'));
    }
}
