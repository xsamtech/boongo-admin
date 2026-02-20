<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use Illuminate\Http\Request;
use App\Http\Resources\File as ResourcesFile;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class FileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = File::orderBy('created_at')->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.find_all_files_success'), $files->lastPage(), $count_files);
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
            'file_name' => $request->file_name,
            'file_url' => $request->file_url,
            'media_length' => $request->media_length,
            'type_id' => $request->type_id,
            'work_id' => $request->work_id,
            'program_id' => $request->program_id,
            'message_id' => $request->message_id
        ];

        // Validate required fields
        if (trim($inputs['file_url']) == null) {
            return $this->handleError($inputs['file_url'], __('validation.required'), 400);
        }

        if ($inputs['type_id'] == null OR $inputs['type_id'] == ' ') {
            return $this->handleError($inputs['type_id'], __('validation.required'), 400);
        }

        if (trim($inputs['file_name']) != null) {
            if (trim($inputs['work_id']) != null AND is_numeric($inputs['work_id'])) {
                // Select all work files to check unique constraint
                $files = File::where('work_id', $inputs['work_id'])->get();

                if ($files != null) {
                    foreach ($files as $another_file):
                        if ($another_file->file_name == $inputs['file_name']) {
                            return $this->handleError($inputs['file_name'], __('validation.custom.file_name.exists'), 400);
                        }
                    endforeach;
                }
            }
        }

        $file = File::create($inputs);

        return $this->handleResponse(new ResourcesFile($file), __('notifications.create_file_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $file = File::find($id);

        if (is_null($file)) {
            return $this->handleError(__('notifications.find_file_404'));
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.find_file_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'file_name' => $request->file_name,
            'file_url' => $request->file_url,
            'media_length' => $request->media_length,
            'type_id' => $request->type_id,
            'work_id' => $request->work_id,
            'program_id' => $request->program_id,
            'message_id' => $request->message_id
        ];

        if (trim($inputs['file_name']) != null) {
            if ($inputs['work_id'] != null) {
                // Select all files and current file to check unique constraint
                $files = File::where('work_id', $inputs['work_id'])->get();
                $current_file = File::find($inputs['id']);

                if ($files != null) {
                    foreach ($files as $another_file):
                        if ($current_file->file_name != $inputs['file_name']) {
                            if ($another_file->file_name == $inputs['file_name']) {
                                return $this->handleError($inputs['file_name'], __('validation.custom.file_name.exists'), 400);
                            }
                        }
                    endforeach;

                    $file->update([
                        'file_name' => $inputs['file_name'],
                        'updated_at' => now()
                    ]);

                } else {
                    $file->update([
                        'file_name' => $inputs['file_name'],
                        'updated_at' => now()
                    ]);
                }

            } else {
                $file->update([
                    'file_name' => $inputs['file_name'],
                    'updated_at' => now()
                ]);
            }
        }

        if ($inputs['file_url'] != null) {
            $file->update([
                'file_url' => $inputs['file_url'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['media_length'] != null) {
            $file->update([
                'media_length' => $inputs['media_length'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $file->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['work_id'] != null) {
            $file->update([
                'work_id' => $inputs['work_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['program_id'] != null) {
            $file->update([
                'program_id' => $inputs['program_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['message_id'] != null) {
            $file->update([
                'message_id' => $inputs['message_id'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesFile($file), __('notifications.update_file_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        $file->delete();

        $files = File::orderBy('created_at')->paginate(10);
        $count_files = File::count();

        return $this->handleResponse(ResourcesFile::collection($files), __('notifications.delete_file_success'), $files->lastPage(), $count_files);
    }
}
