<?php

namespace App\Http\Controllers\Web;

use App\Models\File;
use App\Models\Type;
use App\Models\Work;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class backup_AdminController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Partners page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function work(Request $request)
    {
        if ($request->has('type')) {
            if ($request->get('type') == 'empty') {
                return redirect('/');
            }

            // Group names
            $work_type_group = 'Type d\'œuvre';
            // All types by group
            $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
            // All categories by group
            $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category');
            $works = $this::$api_client_manager::call('GET', getApiURL()  . '/work/find_all_by_type/fr/' . $request->get('type') . ($request->has('page') ? '?page=' . $request->get('page') : ''));

            if ($works->success) {
                return view('work-test', [
                    'types' => $types_by_group->data,
                    'categories' => $categories->data,
                    'works' => $works->data,
                    'lastPage' => $works->lastPage,
                ]);

            } else {
                $all_works = $this::$api_client_manager::call('GET', getApiURL()  . '/work' . ($request->has('page') ? '?page=' . $request->get('page') : ''));
                return view('work-test', [
                    'types' => $types_by_group->data,
                    'categories' => $categories->data,
                    'works' => $all_works->data,
                    'lastPage' => $all_works->lastPage,
                ]);
            }

        } else {
            // User by "username"
            $user_profile = $this::$api_client_manager::call('GET', getApiURL() . '/user/profile/xanderssamoth');
            // Group names
            $work_type_group = 'Type d\'œuvre';
            // All types by group
            $types_by_group = $this::$api_client_manager::call('GET', getApiURL() . '/type/find_by_group/' . $work_type_group);
            // All categories by group
            $categories = $this::$api_client_manager::call('GET', getApiURL() . '/category', $user_profile->data->api_token);
            $works = $this::$api_client_manager::call('GET', getApiURL()  . '/work' . ($request->has('page') ? '?page=' . $request->get('page') : ''), $user_profile->data->api_token);

            return view('work-test', [
                'types' => $types_by_group->data,
                'categories' => $categories->data,
                'works' => $works->data,
                'lastPage' => $works->lastPage,
            ]);
        }
    }

    /**
     * GET: Partners page
     *
     * @return \Illuminate\View\View
     */
    public function partners()
    {
        $partners = $this::$api_client_manager::call('GET', getApiURL() . '/partner');

        return view('partner-test', [
            'partners' => $partners->data,
        ]);
    }

    /**
     * GET: Partner datas
     *
     * @param  $id
     * @return \Illuminate\View\View
     */
    public function partnersDatas($id)
    {
        $partner = $this::$api_client_manager::call('GET', getApiURL() . '/partner/' . $id);

        return view('partner-test', [
            'partner' => $partner->data,
        ]);
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * Store a new work.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function addWork(Request $request)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Get inputs
        $inputs = [
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'video_source' => isset($request->video_source) ? $request->video_source : 'AWS',
            'author' => $request->author,
            'editor' => $request->editor,
            'is_public' => 1,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id
        ];

        // Validate required fields
        if ($inputs['work_title'] == null) {
            return Redirect::back()->with('error_message', __('validation.custom.title.required'));
        }

        if ($inputs['type_id'] == null) {
            return Redirect::back()->with('error_message', __('validation.custom.type_name.required'));
        }

        $work = Work::create($inputs);

        if ($request->categories_ids != null) {
            $categories = $request->input('categories_ids', []);

            $work->categories()->sync($categories);
        }

        if ($request->hasFile('files_urls')) {
            $files = $request->file('files_urls', []);
            $fileNames = $request->input('files_names', []);

            // Types of extensions for different file types
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'mkv', 'webm'];
            $document_extensions = ['pdf', 'doc', 'docx', 'txt'];
            $audio_extensions = ['mp3', 'wav', 'flac'];

            // File browsing
            foreach ($files as $key => $singleFile) {
                // Checking the file extension
                $file_extension = $singleFile->getClientOriginalExtension();

                // File type check
                $custom_uri = '';
                $is_valid_type = false;
                $file_type_id = null;

                if (in_array($file_extension, $image_extensions)) { // File is an image
                    $custom_uri = 'images/works';
                    $file_type_id = $image_type->id;
                    $is_valid_type = true;

                } elseif (in_array($file_extension, $document_extensions)) { // File is a document
                    $custom_uri = 'documents/works';
                    $file_type_id = $document_type->id;
                    $is_valid_type = true;

                } elseif (in_array($file_extension, $audio_extensions)) { // File is an audio
                    $custom_uri = 'audios/works';
                    $file_type_id = $audio_type->id;
                    $is_valid_type = true;
                }

                // If the extension does not match any valid type
                if (!$is_valid_type) {
                    return $this->handleError(__('notifications.type_is_not_file'));
                }

                // Generate a unique path for the file
                $filename = $singleFile->getClientOriginalName();
                $cleaned_filename = sanitizeFileName($filename);
                $file_url =  $custom_uri . '/' . $work->id . '/' . $cleaned_filename ;

                // Upload file
                try {
                    $singleFile->storeAs($custom_uri . '/' . $work->id, $cleaned_filename, 's3');

                } catch (\Throwable $th) {
                    return $this->handleError($th, __('notifications.create_work_file_500'), 500);
                }

                // Creating the database record for the file
                File::create([
                    'file_name' => trim($fileNames[$key] ?? $cleaned_filename),
                    'file_url' => config('filesystems.disks.s3.url') . $file_url,
                    'type_id' => $file_type_id,
                    'work_id' => $work->id
                ]);
            }
        }

        return Redirect::back()->with('success_message', __('notifications.create_work_success'));
    }
}
