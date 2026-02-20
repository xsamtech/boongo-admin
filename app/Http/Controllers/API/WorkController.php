<?php

namespace App\Http\Controllers\API;

use App\Models\File;
use App\Models\Group;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\Session;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\Like as ResourcesLike;
use App\Http\Resources\Partner as ResourcesPartner;
use App\Http\Resources\Session as ResourcesSession;
use App\Http\Resources\User as ResourcesUser;
use App\Http\Resources\Work as ResourcesWork;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class WorkController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $works = Work::orderByDesc('created_at')->paginate(10);
        $count_works = Work::count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_works);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Get app currency
        $latest_subscription = Subscription::orderByDesc('created_at')->latest()->first();
        $selected_currency = $latest_subscription->currency_id;
        // Get inputs
        $inputs = [
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'video_source' => !empty($request->video_source) ? $request->video_source : (!empty($request->work_url) ? 'YouTube' : 'AWS'),
            'media_length' => $request->media_length,
            'author' => $request->author,
            'editor' => $request->editor,
            'is_public' => isset($request->is_public) ? $request->is_public : 0,
            'consultation_price' => $request->input('consultation_price') !== null ? (float) $request->input('consultation_price') : null,
            'number_of_hours' => $request->number_of_hours,
            'currency_id' => $request->filled('currency_id') ? (is_numeric($request->input('currency_id')) ? (int) $request->input('currency_id') : null) : $selected_currency,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'organization_id' => $request->organization_id,
            'category_id' => $request->category_id
        ];

        // Validate required fields
        if ($inputs['work_title'] == null) {
            return $this->handleError($inputs['work_title'], __('validation.custom.title.required'), 400);
        }

        if ($inputs['type_id'] == null) {
            return $this->handleError($inputs['type_id'], __('validation.custom.type_name.required'), 400);
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
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg', 'heif', 'heic', 'ico'];
            $document_extensions = ['pdf', 'doc', 'docx', 'txt'];
            $audio_extensions = ['mp3', 'wav', 'flac'];

            // File browsing
            foreach ($files as $key => $singleFile) {
                // Checking the file extension
                $file_extension = strtolower($singleFile->getClientOriginalExtension());

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
                    // $singleFile->storeAs($custom_uri . '/' . $work->id, $cleaned_filename, 'public');

                } catch (\Throwable $th) {
                    return $this->handleError($th, __('notifications.create_work_file_500'), 500);
                }

                // Creating the database record for the file
                File::create([
                    'file_name' => trim($fileNames[$key] ?? $cleaned_filename),
                    'file_url' => config('filesystems.disks.s3.url') . $file_url,
                    // 'file_url' => Storage::url($file_url),
                    'type_id' => $file_type_id,
                    'work_id' => $work->id
                ]);
            }
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.create_work_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $work = Work::find($id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if ($request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address') or $request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            $session = Session::where('user_id', $request->header('X-user-id'))->first();

            if (is_null($session)) {
                $session = Session::create([
                    'id' => Str::random(255),
                    'ip_address' =>  $request->hasHeader('X-ip-address') ? $request->header('X-ip-address') : null,
                    'user_agent' => $request->header('X-user-agent'),
                    'user_id' => $request->header('X-user-id')
                ]);

                $session->works()->attach([$work->id]);

            } else {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id]);
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching([$work->id]);
                }
            }
        }

        if ($request->hasHeader('X-ip-address')) {
            $session = Session::where('ip_address', $request->header('X-ip-address'))->first();

            if (is_null($session)) {
                $session = Session::create([
                    'id' => Str::random(255),
                    'ip_address' =>  $request->header('X-ip-address'),
                    'user_agent' => $request->header('X-user-agent')
                ]);

                $session->works()->attach([$work->id]);

            } else {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id]);
                }

                if (count($session->works) > 0) {
                    $session->works()->syncWithoutDetaching([$work->id]);
                }
            }
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.find_work_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Work $work)
    {
        // Get inputs
        $inputs = [
            // 'id' => $request->id,
            'work_title' => $request->work_title,
            'work_content' => $request->work_content,
            'work_url' => $request->work_url,
            'video_source' => $request->video_source,
            'media_length' => $request->media_length,
            'author' => $request->author,
            'editor' => $request->editor,
            'is_public' => $request->is_public,
            'consultation_price' => $request->consultation_price,
            'number_of_hours' => $request->number_of_hours,
            'currency_id' => $request->currency_id,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'user_id' => $request->user_id,
            'organization_id' => $request->organization_id,
            'category_id' => $request->category_id
        ];
        // $current_work = Work::find($inputs['id']);

        if ($inputs['work_title'] != null) {
            $work->update([
                'work_title' => $inputs['work_title'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['work_content'] != null) {
            $work->update([
                'work_content' => $inputs['work_content'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['work_url'] != null) {
            $work->update([
                'work_url' => $inputs['work_url'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['video_source'] != null) {
            $work->update([
                'video_source' => $inputs['video_source'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['media_length'] != null) {
            $work->update([
                'media_length' => $inputs['media_length'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['author'] != null) {
            $work->update([
                'author' => $inputs['author'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['editor'] != null) {
            $work->update([
                'editor' => $inputs['editor'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_public'] != null) {
            $work->update([
                'is_public' => $inputs['is_public'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['consultation_price'] != null) {
            $work->update([
                'consultation_price' => $inputs['consultation_price'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['number_of_hours'] != null) {
            $work->update([
                'number_of_hours' => $inputs['number_of_hours'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['currency_id'] != null) {
            $work->update([
                'currency_id' => $inputs['currency_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['type_id'] != null) {
            $work->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $work->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['user_id'] != null) {
            $work->update([
                'user_id' => $inputs['user_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['organization_id'] != null) {
            $work->update([
                'organization_id' => $inputs['organization_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['category_id'] != null) {
            $work->update([
                'category_id' => $inputs['category_id'],
                'updated_at' => now(),
            ]);
        }

        if ($request->categories_ids == null) {
            if (count($work->categories) > 0) {
                $work->categories()->detach();
            }

        } else {
            $work->categories()->sync($request->categories_ids);
        }

        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Work  $work
     * @return \Illuminate\Http\Response
     */
    public function destroy(Work $work)
    {
        $notifications = Notification::where('work_id', $work->id)->get();

        if ($notifications != null) {
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        }

        $work->delete();

        $works = Work::all();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.delete_work_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find current trends.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $year
     * @return \Illuminate\Http\Response
     */
    public function trends(Request $request, $year)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();

        if ($request->hasHeader('X-user-id')) {
            // User
            $logged_in_user = User::find($request->header('X-user-id'));

            if (is_null($logged_in_user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            // Subscription
            $is_subscribed = $logged_in_user->hasValidSubscription();

            // If user is subscribed, send only data of the same category as that in the subscription
            if ($is_subscribed) {
                $valid_subscription = $logged_in_user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $works = Work::whereHas('sessions', function ($query) use ($year) {
                                    $query->whereYear('sessions.created_at', '=', $year);
                                })->where('category_id', $valid_subscription->category_id)->distinct()->limit(7)->get()->reverse()->values();
                $count_all = Work::whereHas('sessions', function ($query) use ($year) {
                                        $query->whereYear('sessions.created_at', '=', $year);
                                    })->where('category_id', $valid_subscription->category_id)->distinct()->count();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all, $partnerResource);

            // Otherwise, send all data
            } else {
                $works = Work::whereHas('sessions', function ($query) use ($year) {
                            $query->whereYear('sessions.created_at', '=', $year);
                        })->distinct()->limit(7)->get()->reverse()->values();
                $count_all = Work::whereHas('sessions', function ($query) use ($year) {
                            $query->whereYear('sessions.created_at', '=', $year);
                        })->distinct()->count();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all, $partnerResource);
            }

        } else {
            $works = Work::whereHas('sessions', function ($query) use ($year) {
                                $query->whereYear('sessions.created_at', '=', $year);
                            })->distinct()->limit(7)->get()->reverse()->values();
            $count_all = Work::whereHas('sessions', function ($query) use ($year) {
                                    $query->whereYear('sessions.created_at', '=', $year);
                                })->distinct()->count();
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
            $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

            return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), null, $count_all, $partnerResource);
        }
    }

    /**
     * Get by user entity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $entity
     * @param  int $entity_id
     * @return \Illuminate\Http\Response
     */
    public function findAllByEntity(Request $request, $entity, $entity_id)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();

        if ($entity == 'user') {
            $user = User::find($entity_id);

            if (is_null($user)) {
                return $this->handleError(__('notifications.find_user_404'));
            }

            if ($request->hasHeader('X-user-id')) {
                // Logged in user
                $logged_in_user = User::find($request->header('X-user-id'));

                if (is_null($logged_in_user)) {
                    return $this->handleError(__('notifications.find_user_404') . ' LOGGED IN');
                }

                // Subscription
                $is_subscribed = $logged_in_user->hasValidSubscription();

                if ($is_subscribed) {
                    $valid_subscription = $logged_in_user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                    $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                            $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                        })->where(function ($query) use ($users_ids) {
                                            $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                        })->inRandomOrder()->first() : null;

                    $query = Work::query();

                    $query->where('user_id', $user->id);

                    // Add dynamic conditions
                    $query->when($request->type_id, function ($query) use ($request) {
                        return $query->where('type_id', $request->type_id);
                    });

                    $query->when($request->status_id, function ($query) use ($request) {
                        return $query->where('status_id', $request->status_id);
                    });

                    // Retrieves the query results
                    $works = $query->orderByDesc('updated_at')->paginate(10);
                    $count_all = $query->count();
                    $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                    return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);

                } else {
                    $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                            $query->where('category_partner.status_id', $active_status->id);
                                        })->where(function ($query) use ($users_ids) {
                                            $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                        })->inRandomOrder()->first() : null;

                    $query = Work::query();

                    $query->where('user_id', $user->id);

                    // Add dynamic conditions
                    $query->when($request->type_id, function ($query) use ($request) {
                        return $query->where('type_id', $request->type_id);
                    });

                    $query->when($request->status_id, function ($query) use ($request) {
                        return $query->where('status_id', $request->status_id);
                    });

                    // Retrieves the query results
                    $works = $query->orderByDesc('updated_at')->paginate(10);
                    $count_all = $query->count();
                    $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                    return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
                }

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();

                $query->where('user_id', $user->id);

                // Add dynamic conditions
                $query->when($request->type_id, function ($query) use ($request) {
                    return $query->where('type_id', $request->type_id);
                });

                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                // Retrieves the query results
                $works = $query->orderByDesc('updated_at')->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
            }
        }

        if ($entity == 'organization') {
            $organization = Organization::find($entity_id);

            if (is_null($organization)) {
                return $this->handleError(__('notifications.find_organization_404'));
            }

            if ($request->hasHeader('X-user-id')) {
                // Logged in user
                $logged_in_user = User::find($request->header('X-user-id'));

                if (is_null($logged_in_user)) {
                    return $this->handleError(__('notifications.find_user_404') . ' LOGGED IN');
                }

                // Subscription
                $is_subscribed = $logged_in_user->hasValidSubscription();

                if ($is_subscribed) {
                    $valid_subscription = $logged_in_user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                    $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                            $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                        })->where(function ($query) use ($users_ids) {
                                            $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                        })->inRandomOrder()->first() : null;

                    $query = Work::query();

                    $query->where('organization_id', $organization->id);

                    // Add dynamic conditions
                    $query->when($request->type_id, function ($query) use ($request) {
                        return $query->where('type_id', $request->type_id);
                    });

                    $query->when($request->status_id, function ($query) use ($request) {
                        return $query->where('status_id', $request->status_id);
                    });

                    // Retrieves the query results
                    $works = $query->orderByDesc('updated_at')->paginate(10);
                    $count_all = $query->count();
                    $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                    return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);

                } else {
                    $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                            $query->where('category_partner.status_id', $active_status->id);
                                        })->where(function ($query) use ($users_ids) {
                                            $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                        })->inRandomOrder()->first() : null;
                    $query = Work::query();

                    $query->where('organization_id', $organization->id);

                    // Add dynamic conditions
                    $query->when($request->type_id, function ($query) use ($request) {
                        return $query->where('type_id', $request->type_id);
                    });

                    $query->when($request->status_id, function ($query) use ($request) {
                        return $query->where('status_id', $request->status_id);
                    });

                    // Retrieves the query results
                    $works = $query->orderByDesc('updated_at')->paginate(10);
                    $count_all = $query->count();
                    $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                    return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
                }

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();

                $query->where('organization_id', $organization->id);

                // Add dynamic conditions
                $query->when($request->type_id, function ($query) use ($request) {
                    return $query->where('type_id', $request->type_id);
                });

                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                // Retrieves the query results
                $works = $query->orderByDesc('updated_at')->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
            }
        }
    }

    /**
     * Get by type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $locale
     * @param  string $type_name
     * @return \Illuminate\Http\Response
     */
    public function findAllByType(Request $request, $locale, $type_name)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Request
        $type = Type::where('type_name->' . $locale, $type_name)->first();

        if (is_null($type)) {
            return $this->handleError(__('notifications.find_type_404'));
        }

        if ($request->hasHeader('X-user-id')) {
            // Logged in user
            $logged_in_user = User::find($request->header('X-user-id'));

            if (is_null($logged_in_user)) {
                return $this->handleError(__('notifications.find_user_404') . ' LOGGED IN');
            }

            // Subscription
            $is_subscribed = $logged_in_user->hasValidSubscription();

            if ($is_subscribed) {
                $valid_subscription = $logged_in_user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();

                $query->where('type_id', $type->id);

                // Add dynamic conditions
                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                $works = $query->orderByDesc('created_at')->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();

                $query->where('type_id', $type->id);

                // Add dynamic conditions
                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                $works = $query->orderByDesc('created_at')->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
            $query = Work::query();

            $query->where('type_id', $type->id);

            // Add dynamic conditions
            $query->when($request->status_id, function ($query) use ($request) {
                return $query->where('status_id', $request->status_id);
            });

            $works = $query->orderByDesc('created_at')->paginate(10);
            $count_all = $query->count();
            $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

            return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
        }
    }

    /**
     * Find work views.
     *
     * @param  int  $work_id
     * @return \Illuminate\Http\Response
     */
    public function findViews($work_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $sessions = Session::whereHas('works', function ($query) use ($work) {
                                    // $query->where('work_session.read', 1)
                                    $query->where('work_session.work_id', $work->id)
                                        ->orderByDesc('work_session.created_at');
                                })->paginate(10);
        $count_all = Session::whereHas('works', function ($query) use ($work) {
                                    // $query->where('work_session.read', 1)
                                    $query->where('work_session.work_id', $work->id)
                                        ->orderByDesc('work_session.created_at');
                                })->count();

        return $this->handleResponse(ResourcesSession::collection($sessions), __('notifications.find_all_sessions_success'), $sessions->lastPage(), $count_all);
    }

    /**
     * Find work likes.
     *
     * @param  int  $work_id
     * @return \Illuminate\Http\Response
     */
    public function findLikes($work_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        $likes = Like::where('for_work_id', $work->id)->orderByDesc('created_at')->paginate(10);
        $count_all = Like::where('for_work_id', $work->id)->count();

        return $this->handleResponse(ResourcesLike::collection($likes), __('notifications.find_all_likes_success'), $likes->lastPage(), $count_all);
    }

    /**
     * Switch the work view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $work_id
     * @return \Illuminate\Http\Response
     */
    public function switchView(Request $request, $work_id)
    {
        $work = Work::find($work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if (!$request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            return $this->handleError(__('validation.custom.owner.required'));
        }

        if ($request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address') or $request->hasHeader('X-user-id') and !$request->hasHeader('X-ip-address')) {
            $session = Session::where('user_id', $request->header('X-user-id'))->first();

            if (!empty($session)) {
                if (count($session->works) == 0) {
                    $session->works()->attach([$work->id => ['read' => 1]]);
                }

                if (count($session->works) > 0) {
                    foreach ($session->works as $work) {
                        $session->works()->syncWithoutDetaching([$work->id => ['read' => ($work->pivot->read == 1 ? 0 : 1)]]);
                    }
                }

                if ($work->user_id != null) {
                    // Groups
                    $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
                    $notification_type_group = Group::where('group_name', 'Type de notification')->first();
                    // Status
                    $status_unread = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
                    // Type
                    $type_consulting = Type::where([['type_name->fr', 'Consultation d\'œuvre'], ['group_id', $notification_type_group->id]])->first();
                    $visitor = User::find($request->header('X-user-id'));

                    if (is_null($visitor)) {
                        return $this->handleError(__('notifications.find_visitor_404'));
                    }

                    /*
                        HISTORY AND/OR NOTIFICATION MANAGEMENT
                     */
                    if (!empty($visitor)) {
                        Notification::create([
                            'type_id' => $type_consulting->id,
                            'status_id' => $status_unread->id,
                            'from_user_id' => $visitor->id,
                            'to_user_id' => $work->user_id,
                            'work_id' => $work->id
                        ]);
                    }
                }
            }
        }

        if (!$request->hasHeader('X-user-id') and $request->hasHeader('X-ip-address')) {
            $session = Session::where('ip_address', $request->header('X-ip-address'))->first();

            if (!empty($session)) {
                if ($session->works() == null) {
                    $session->works()->attach([$work->id => ['read' => 1]]);
                }

                if ($session->works() != null) {
                    foreach ($session->works as $work) {
                        $session->works()->syncWithoutDetaching([$work->id => ['read' => ($work->pivot->read == 1 ? 0 : 1)]]);
                    }
                }
            }
        }
    }

    /**
     * Get all by title.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $query = Work::query();
        $categories = array_filter(Arr::wrap($request->input('categories_ids')));

        if (count($categories) > 0) {
            // Filter works having at least one of the specified categories
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('categories.id', $categories);
            });
        }

        // Include uncategorized works if no category is specified
        $query->where('work_title', 'LIKE', '%' . $request->data . '%')->orderByDesc('works.created_at');

        // Add dynamic conditions
        $query->when($request->type_id, function ($query) use ($request) {
            return $query->where('type_id', $request->type_id);
        });

        $query->when($request->status_id, function ($query) use ($request) {
            return $query->where('status_id', $request->status_id);
        });

        $works = $query->paginate(10);
        $count_all = $query->count();

        return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all);
    }

    /**
     * Edit some files in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $type_id
     * @param  int $work_id
     * @return \Illuminate\Http\Response
     */
    public function uploadFiles(Request $request)
    {
        // Group
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        // Types
        $image_type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $document_type = Type::where([['type_name->fr', 'Document'], ['group_id', $file_type_group->id]])->first();
        $audio_type = Type::where([['type_name->fr', 'Audio'], ['group_id', $file_type_group->id]])->first();
        // Request
        $work = Work::find($request->work_id);

        if (is_null($work)) {
            return $this->handleError(__('notifications.find_work_404'));
        }

        if ($request->hasFile('video_file_url')) {
            if ($request->image_file_type_id == null) {
                return $this->handleError($request->image_file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->image_file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            if ($type->id != $image_type->id) {
                return $this->handleError(__('notifications.type_is_not_file') . ' ' . $image_type->type_name);
            }

            $file = $request->file('video_file_url');
            $filename = $file->getClientOriginalName();
            $file_url =  'images/works/' . $work->id . '/' . $filename;

            // Upload file
            // $dir_result = Storage::url(Storage::disk('public')->put($file_url, $file));
            try {
                $file->storeAs('images/works/' . $work->id, $filename, 's3');

            } catch (\Throwable $th) {
                return $this->handleError($th, __('notifications.create_work_file_500'), 500);
            }

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
                'file_url' => config('filesystems.disks.s3.url') . $file_url, // $dir_result
                'type_id' => $type->id,
                'work_id' => $work->id
            ]);
        }

        if ($request->hasFile('document_file_url')) {
            if ($request->document_file_type_id == null) {
                return $this->handleError($request->document_file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->document_file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            if ($type->id != $document_type->id) {
                return $this->handleError(__('notifications.type_is_not_file') . ' ' . $document_type->type_name);
            }

            $file = $request->file('document_file_url');
            $filename = $file->getClientOriginalName();
            $file_url =  'documents/works/' . $work->id . '/' . $filename;

            // Upload file
            // $dir_result = Storage::url(Storage::disk('public')->put($file_url, $file));
            try {
                $file->storeAs('documents/works/' . $work->id, $filename, 's3');

            } catch (\Throwable $th) {
                return $this->handleError($th, __('notifications.create_work_file_500'), 500);
            }

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
                'file_url' => config('filesystems.disks.s3.url') . $file_url, // $dir_result
                'type_id' => $type->id,
                'work_id' => $work->id
            ]);
        }

        if ($request->hasFile('audio_file_url')) {
            if ($request->audio_file_type_id == null) {
                return $this->handleError($request->audio_file_type_id, __('validation.required') . ' (' . __('miscellaneous.file_type') . ') ', 400);
            }

            $type = Type::find($request->audio_file_type_id);

            if (is_null($type)) {
                return $this->handleError(__('notifications.find_type_404'));
            }

            if ($type->id != $audio_type->id) {
                return $this->handleError(__('notifications.type_is_not_file') . ' ' . $audio_type->type_name);
            }

            $file = $request->file('audio_file_url');
            $filename = $file->getClientOriginalName();
            $file_url =  'audios/works/' . $work->id . '/' . $filename;

            // Upload file
            // $dir_result = Storage::url(Storage::disk('public')->put($file_url, $file));
            try {
                $file->storeAs('audios/works/' . $work->id, $filename, 's3');

            } catch (\Throwable $th) {
                return $this->handleError($th, __('notifications.create_work_file_500'), 500);
            }

            File::create([
                'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
                'file_url' => config('filesystems.disks.s3.url') . $file_url, // $dir_result
                'type_id' => $type->id,
                'work_id' => $work->id
            ]);
        }
        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }

    /**
     * Filter works by categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function filterByCategories(Request $request)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();

        if ($request->hasHeader('X-user-id')) {
            // Logged in user
            $logged_in_user = User::find($request->header('X-user-id'));

            if (is_null($logged_in_user)) {
                return $this->handleError(__('notifications.find_user_404') . ' LOGGED IN');
            }

            // Subscription
            $is_subscribed = $logged_in_user->hasValidSubscription();

            if ($is_subscribed) {
                $valid_subscription = $logged_in_user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();
                $categories = array_filter(Arr::wrap($request->input('categories_ids')));

                if (count($categories) > 0) {
                    // Filter works having at least one of the specified categories
                    $query->whereHas('categories', function ($q) use ($categories) {
                        $q->whereIn('categories.id', $categories);
                    });
                }

                // Include uncategorized works if no category is specified
                $query->orderByDesc('works.created_at');

                // Add dynamic conditions
                $query->when($request->type_id, function ($query) use ($request) {
                    return $query->where('type_id', $request->type_id);
                });

                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });

                $query->when($request->organization_id, function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                });

                $works = $query->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
                $query = Work::query();
                $categories = array_filter(Arr::wrap($request->input('categories_ids')));

                if (count($categories) > 0) {
                    // Filter works having at least one of the specified categories
                    $query->whereHas('categories', function ($q) use ($categories) {
                        $q->whereIn('categories.id', $categories);
                    });
                }

                // Include uncategorized works if no category is specified
                $query->orderByDesc('works.created_at');

                // Add dynamic conditions
                $query->when($request->type_id, function ($query) use ($request) {
                    return $query->where('type_id', $request->type_id);
                });

                $query->when($request->status_id, function ($query) use ($request) {
                    return $query->where('status_id', $request->status_id);
                });

                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });

                $query->when($request->organization_id, function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                });

                $works = $query->paginate(10);
                $count_all = $query->count();
                $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

                return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
            $query = Work::query();
            $categories = array_filter(Arr::wrap($request->input('categories_ids')));

            if (count($categories) > 0) {
                // Filter works having at least one of the specified categories
                $query->whereHas('categories', function ($q) use ($categories) {
                    $q->whereIn('categories.id', $categories);
                });
            }

            // Include uncategorized works if no category is specified
            $query->orderByDesc('works.created_at');

            // Add dynamic conditions
            $query->when($request->type_id, function ($query) use ($request) {
                return $query->where('type_id', $request->type_id);
            });

            $query->when($request->status_id, function ($query) use ($request) {
                return $query->where('status_id', $request->status_id);
            });

            $query->when($request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            });

            $query->when($request->organization_id, function ($query) use ($request) {
                return $query->where('organization_id', $request->organization_id);
            });

            $works = $query->paginate(10);
            $count_all = $query->count();
            $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

            return $this->handleResponse(ResourcesWork::collection($works), __('notifications.find_all_works_success'), $works->lastPage(), $count_all, $partnerResource);
        }
    }

    /**
     * Validate a user subscriptions.
     *
     * @param  int $user_id
     */
    public function validateConsultations($user_id)
    {
        // Groups
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        $payment_status_group = Group::where('group_name', 'Etat du paiement')->first();
        // Status
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        $done_status = Status::where([['status_name->fr', 'Effectué'], ['group_id', $payment_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $last_consultation_cart = $user->carts()->where([['entity', 'consultation'], ['status_id', $paid_status->id]])->latest()->first();

        if (!$last_consultation_cart) {
            return $this->handleError(__('notifications.find_subscription_404'));
        }

        $cart_payment = Payment::find($last_consultation_cart->payment_id);

        if (is_null($cart_payment)) {
            return $this->handleError(__('notifications.find_payment_404'));
        }

        // Check if payment linked to this cart is done
        if ($cart_payment->status_id == $done_status->id) {
            $worksIds = $last_consultation_cart->works()->pluck('works.id')->toArray();

            // Update all works linked to this cart in the pivot table "cart_work"
            foreach ($worksIds as $id) {
                $last_consultation_cart->works()->updateExistingPivot($id, [
                    'status_id' => $valid_status->id // We update the "status_id" in the pivot
                ]);
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));

        } else {
            return $this->handleResponse(new ResourcesUser($user), __('notifications.find_done_payment_404'));
        }
    }

    /**
     * Invalidate a user consultations.
     *
     * @param  int $user_id
     */
    public function invalidateConsultations($user_id)
    {
        // Groups
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        $expired_status = Status::where([['status_name->fr', 'Expiré'], ['group_id', $subscription_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        // Retrieve the last cart where the entity is "consultation"
        $last_consultation_cart = $user->carts()->where([['entity', 'consultation'], ['status_id', $paid_status->id]])->latest()->first();

        if (is_null($last_consultation_cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        $updated_at = Carbon::parse($last_consultation_cart->updated_at);

        // Check if the update date is older than 30 days
        if ($updated_at->diffInDays(Carbon::now()) >= 30) {
            $worksIds = $last_consultation_cart->works()->wherePivot('status_id', $valid_status->id)->pluck('works.id')->toArray();

            // Expire consultation for each work in the cart
            foreach ($worksIds as $id) {
                $last_consultation_cart->works()->updateExistingPivot($id, [
                    'status_id' => $expired_status->id // We update the "status_id" in the pivot
                ]);
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.expire_consultation_success'));

        } else {
            return $this->handleResponse(new ResourcesUser($user), __('notifications.expire_consultation_failed'));
        }
    }

    /**
     * Update work picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function addImage(Request $request, $id)
    {
        $file_type_group = Group::where('group_name', 'Type de fichier')->first();
        $type = Type::where([['type_name->fr', 'Image (Photo/Vidéo)'], ['group_id', $file_type_group->id]])->first();
        $inputs = [
            'work_id' => $request->work_id,
            'image_64' => $request->image_64
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);
        // Create image URL
		$image_url = 'images/works/' . $id . '/' . Str::random(50) . '.png';

		// Upload image
		Storage::url(Storage::disk('public')->put($image_url, base64_decode($image)));

		$work = Work::find($id);

        File::create([
            'file_name' => trim($request->file_name) != null ? $request->file_name : $work->work_title,
            'file_url' => getWebURL() . '/storage/' . $image_url,
            'type_id' => $type->id,
            'work_id' => $work->id
        ]);

        return $this->handleResponse(new ResourcesWork($work), __('notifications.update_work_success'));
    }
}
