<?php

namespace App\Http\Controllers\API;

use stdClass;
use App\Mail\OTPCode;
use App\Models\Circle;
use App\Models\Currency;
use App\Models\Event;
use App\Models\Group;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\PasswordReset;
use App\Models\PersonalAccessToken;
use App\Models\Status;
use App\Models\ToxicContent;
use App\Models\Type;
use App\Models\User;
use Nette\Utils\Random;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Circle as ResourcesCircle;
use App\Http\Resources\Event as ResourcesEvent;
use App\Http\Resources\Organization as ResourcesOrganization;
use App\Http\Resources\Partner as ResourcesPartner;
use App\Http\Resources\PasswordReset as ResourcesPasswordReset;
use App\Http\Resources\ToxicContent as ResourcesToxicContent;
use App\Http\Resources\User as ResourcesUser;
use App\Services\InfobipService;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class UserController extends BaseController
{
    protected $infobipService;

    public function __construct(InfobipService $infobipService)
    {
        $this->infobipService = $infobipService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'));
    }

    /**
     * Store a resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Groups
        $user_status_group = Group::where('group_name', 'Etat de l\'utilisateur')->first();
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Statuses
        $status_activated = Status::where([['status_name->fr', 'Activé'], ['group_id', $user_status_group->id]])->first();
        $status_unread = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Type
        $type_new_user = Type::where([['type_name->fr', 'Nouvel utilisateur'], ['group_id', $notification_type_group->id]])->first();
        // Currency
        $currency_american_dollar = Currency::where('currency_acronym', 'USD')->first();
        // Get inputs
        $inputs = [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'surname' => $request->surname,
            'gender' => isset($request->gender) ? $request->password : 'M',
            'birthdate' => $request->birthdate,
            'city' => $request->city,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'p_o_box' => $request->p_o_box,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => empty($request->password) ? null : Hash::make($request->password),
            'country_id' => $request->country_id,
            'currency_id' => !empty($request->currency_id) ? $request->currency_id : $currency_american_dollar->id,
            'status_id' => is_null($status_activated) ? null : $status_activated->id
        ];
        $users = User::all();
        $password_resets = PasswordReset::all();
        $object = new stdClass();
        // $basic  = new \Vonage\Client\Credentials\Basic(config('vonage.api_key'), config('vonage.api_secret'));
        // $client = new \Vonage\Client($basic);

        // "email" and "phone" cannot all be NULL
        if (trim($inputs['email']) == null AND trim($inputs['phone']) == null) {
            return $this->handleError($inputs['email'], __('validation.custom.email_or_phone.required'), 400);
        }

        if ($inputs['email'] != null) {
            // Check if user email already exists
            foreach ($users as $another_user):
                if ($another_user->email == $inputs['email']) {
                    return $this->handleError($inputs['email'], __('validation.custom.email.exists'), 400);
                }
            endforeach;

            // If email exists in "password_reset" table, delete it
            if ($password_resets != null) {
                foreach ($password_resets as $password_reset):
                    if ($password_reset->email == $inputs['email']) {
                        $password_reset->delete();
                    }
                endforeach;
            }
        }

        if ($inputs['phone'] != null) {
            // Check if user phone already exists
            foreach ($users as $another_user):
                if ($another_user->phone == $inputs['phone']) {
                    return $this->handleError($inputs['phone'], __('validation.custom.phone.exists'), 400);
                }
            endforeach;

            // If phone exists in "password_reset" table, delete it
            if ($password_resets != null) {
                foreach ($password_resets as $password_reset):
                    if ($password_reset->phone == $inputs['phone']) {
                        $password_reset->delete();
                    }
                endforeach;
            }
        }

        if ($inputs['username'] != null) {
            // Check if username already exists
            foreach ($users as $another_user):
                if ($another_user->username == $inputs['username']) {
                    return $this->handleError($inputs['username'], __('validation.custom.username.exists'), 400);
                }
            endforeach;
        }

        if ($inputs['password'] != null) {
            if ($request->confirm_password != $request->password OR $request->confirm_password == null) {
                return $this->handleError($request->confirm_password, __('notifications.confirm_password_error'), 400);
            }

            // if (preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $inputs['password']) == 0) {
            //     return $this->handleError($inputs['password'], __('miscellaneous.password.error'), 400);
            // }

            $random_int_stringified = (string) random_int(1000000, 9999999);

            if ($inputs['email'] != null AND $inputs['phone'] != null) {
                $password_reset = PasswordReset::create([
                    'email' => $inputs['email'],
                    'phone' => $inputs['phone'],
                    'token' => $random_int_stringified,
                    'former_password' => $request->password
                ]);

                Mail::to($inputs['email'])->send(new OTPCode($password_reset->token));

                $sendSMS = $this->infobipService->sendMessage($password_reset->phone, (string) $password_reset->token);

                if (!$sendSMS['success']) {
                    return $this->handleError('Oups!', $sendSMS['message'], 500);
                }

                // try {
                //     $client->sms()->send(new \Vonage\SMS\Message\SMS($password_reset->phone, 'Boongo', (string) $password_reset->token));

                // } catch (\Throwable $th) {
                //     return $this->handleError($th->getMessage(), __('notifications.create_user_SMS_failed'), 500);
                // }

                $object->password_reset = new ResourcesPasswordReset($password_reset);

            } else {
                if ($inputs['email'] != null AND $inputs['phone'] == null) {
                    $password_reset = PasswordReset::create([
                        'email' => $inputs['email'],
                        'token' => $random_int_stringified,
                        'former_password' => $request->password
                    ]);

                    Mail::to($inputs['email'])->send(new OTPCode($password_reset->token));

                    $object->password_reset = new ResourcesPasswordReset($password_reset);
                }

                if ($inputs['email'] == null AND $inputs['phone'] != null) {
                    $password_reset = PasswordReset::create([
                        'phone' => $inputs['phone'],
                        'token' => $random_int_stringified,
                        'former_password' => $request->password
                    ]);

                    $sendSMS = $this->infobipService->sendMessage($password_reset->phone, (string) $password_reset->token);

                    if (!$sendSMS['success']) {
                        return $this->handleError('Oups!', $sendSMS['message'], 500);
                    }

                    // try {
                    //     $client->sms()->send(new \Vonage\SMS\Message\SMS($password_reset->phone, 'Boongo', (string) $password_reset->token));

                    // } catch (\Throwable $th) {
                    //     return $this->handleError($th->getMessage(), __('notifications.create_user_SMS_failed'), 500);
                    // }

                    $object->password_reset = new ResourcesPasswordReset($password_reset);
                }
            }
        }

        if ($inputs['password'] == null) {
            $random_int_stringified = (string) random_int(1000000, 9999999);

            if ($inputs['email'] != null AND $inputs['phone'] != null) {
                $password_reset = PasswordReset::create([
                    'email' => $inputs['email'],
                    'phone' => $inputs['phone'],
                    'token' => $random_int_stringified,
                    'former_password' => Random::generate(10, 'a-zA-Z'),
                ]);

                $inputs['password'] = Hash::make($password_reset->former_password);

                Mail::to($inputs['email'])->send(new OTPCode($password_reset->token));

                $sendSMS = $this->infobipService->sendMessage($password_reset->phone, (string) $password_reset->token);

                if (!$sendSMS['success']) {
                    return $this->handleError('Oups!', $sendSMS['message'], 500);
                }

                // try {
                //     $client->sms()->send(new \Vonage\SMS\Message\SMS($password_reset->phone, 'Boongo', (string) $password_reset->token));

                // } catch (\Throwable $th) {
                //     return $this->handleError($th->getMessage(), __('notifications.create_user_SMS_failed'), 500);
                // }

                $object->password_reset = new ResourcesPasswordReset($password_reset);

            } else {
                if ($inputs['email'] != null AND $inputs['phone'] == null) {
                    $password_reset = PasswordReset::create([
                        'email' => $inputs['email'],
                        'token' => $random_int_stringified,
                        'former_password' => Random::generate(10, 'a-zA-Z')
                    ]);

                    Mail::to($inputs['email'])->send(new OTPCode($password_reset->token));

                    $object->password_reset = new ResourcesPasswordReset($password_reset);

                    $inputs['password'] = Hash::make($password_reset->former_password);
                }

                if ($inputs['email'] == null AND $inputs['phone'] != null) {
                    $password_reset = PasswordReset::create([
                        'phone' => $inputs['phone'],
                        'token' => $random_int_stringified,
                        'former_password' => Random::generate(10, 'a-zA-Z')
                    ]);

                    $sendSMS = $this->infobipService->sendMessage($password_reset->phone, (string) $password_reset->token);

                    if (!$sendSMS['success']) {
                        return $this->handleError('Oups!', $sendSMS['message'], 500);
                    }

                    // try {
                    //     $client->sms()->send(new \Vonage\SMS\Message\SMS($password_reset->phone, 'Boongo', (string) $password_reset->token));

                    // } catch (\Throwable $th) {
                    //     return $this->handleError($th->getMessage(), __('notifications.create_user_SMS_failed'), 500);
                    // }

                    $object->password_reset = new ResourcesPasswordReset($password_reset);

                    $inputs['password'] = Hash::make($password_reset->former_password);
                }
            }
        }

        $user = User::create($inputs);

        if ($request->role_id != null) {
            $user->roles()->attach([$request->role_id]);
        }

        if ($request->organization_id != null) {
            $user->organizations()->attach([$request->organization_id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->update([
            'api_token' => $token,
            'updated_at' => now()
        ]);

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        Notification::create([
            'type_id' => is_null($type_new_user) ? null : $type_new_user->id,
            'status_id' => is_null($status_unread) ? null : $status_unread->id,
            'to_user_id' => $user->id
        ]);

        $object->user = new ResourcesUser($user);

        return $this->handleResponse($object, __('notifications.create_user_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if (!empty($user->email)) {
            $password_reset_email = PasswordReset::where('email', $user->email)->first();

            $object = new stdClass();
            $object->password_reset = new ResourcesPasswordReset($password_reset_email);
            $object->user = new ResourcesUser($user);

            return $this->handleResponse($object, __('notifications.find_user_success'));
        }

        if (!empty($user->phone)) {
            $password_reset_phone = PasswordReset::where('phone', $user->phone)->first();

            $object = new stdClass();
            $object->password_reset = new ResourcesPasswordReset($password_reset_phone);
            $object->user = new ResourcesUser($user);

            return $this->handleResponse($object, __('notifications.find_user_success'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // Get inputs
        $inputs = [
            'id' => $request->id,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'surname' => $request->surname,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'city' => $request->city,
            'address_1' => $request->address_1,
            'address_2' => $request->address_2,
            'p_o_box' => $request->p_o_box,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => $request->password,
            'confirm_password' => $request->confirm_password,
            'email_verified_at' => $request->email_verified_at,
            'phone_verified_at' => $request->phone_verified_at,
            'email_frequency' => $request->email_frequency,
            'two_factor_secret' => $request->two_factor_secret,
            'two_factor_recovery_codes' => $request->two_factor_recovery_codes,
            'two_factor_confirmed_at' => $request->two_factor_confirmed_at,
            'two_factor_phone_confirmed_at' => $request->two_factor_phone_confirmed_at,
            'is_incognito' => $request->is_incognito,
            'promo_code' => $request->promo_code,
            'is_promoted' => $request->is_promoted,
            'country_id' => $request->country_id,
            'currency_id' => $request->currency_id,
            'status_id' => $request->status_id
        ];
        $users = User::all();
        $current_user = User::find($inputs['id']);

        if ($inputs['firstname'] != null) {
            $user->update([
                'firstname' => $inputs['firstname'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['lastname'] != null) {
            $user->update([
                'lastname' => $inputs['lastname'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['surname'] != null) {
            $user->update([
                'surname' => $inputs['surname'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['gender'] != null) {
            $user->update([
                'gender' => $inputs['gender'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['birthdate'] != null) {
            $user->update([
                'birthdate' => $inputs['birthdate'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['city'] != null) {
            $user->update([
                'city' => $inputs['city'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['address_1'] != null) {
            $user->update([
                'address_1' => $inputs['address_1'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['address_2'] != null) {
            $user->update([
                'address_2' => $inputs['address_2'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['p_o_box'] != null) {
            $user->update([
                'p_o_box' => $inputs['p_o_box'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['email'] != null) {
            // Check if email already exists
            foreach ($users as $another_user):
                if (!empty($current_user->email)) {
                    if ($current_user->email != $inputs['email']) {
                        if ($another_user->email == $inputs['email']) {
                            return $this->handleError($inputs['email'], __('validation.custom.email.exists'), 400);
                        }
                    }
                }
            endforeach;

            if ($current_user->email != $inputs['email']) {
                $user->update([
                    'email' => $inputs['email'],
                    'email_verified_at' => null,
                    'updated_at' => now(),
                ]);

            } else {
                $user->update([
                    'email' => $inputs['email'],
                    'updated_at' => now(),
                ]);
            }

            if (!empty($current_user->phone)) {
                $password_reset_by_phone = PasswordReset::where('phone', $current_user->phone)->first();
                $random_int_stringified = (string) random_int(1000000, 9999999);

                if ($password_reset_by_phone != null) {
                    if (!empty($password_reset_by_phone->email)) {
                        if ($password_reset_by_phone->email != $inputs['email']) {
                            $password_reset_by_phone->update([
                                'email' => $inputs['email'],
                                'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, '0-9a-zA-Z'),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    if (empty($password_reset_by_phone->email)) {
                        $password_reset_by_phone->update([
                            'email' => $inputs['email'],
                            'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, '0-9a-zA-Z'),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($password_reset_by_phone == null) {
                    $password_reset_by_email = PasswordReset::where('email', $inputs['email'])->first();

                    if ($password_reset_by_email == null) {
                        PasswordReset::create([
                            'email' => $inputs['email'],
                            'phone' => $current_user->phone,
                            'token' => $random_int_stringified,
                            'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, 'a-zA-Z'),
                        ]);
                    }
                }

            } else {
                $random_int_stringified = (string) random_int(1000000, 9999999);

                PasswordReset::create([
                    'email' => $inputs['email'],
                    'token' => $random_int_stringified,
                    'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, 'a-zA-Z'),
                ]);
            }
        }

        if ($inputs['phone'] != null) {
            // Check if phone already exists
            foreach ($users as $another_user):
                if (!empty($current_user->phone)) {
                    if ($current_user->phone != $inputs['phone']) {
                        if ($another_user->phone == $inputs['phone']) {
                            return $this->handleError($inputs['phone'], __('validation.custom.phone.exists'), 400);
                        }
                    }
                }
            endforeach;

            if ($current_user->phone != $inputs['phone']) {
                $user->update([
                    'phone' => $inputs['phone'],
                    'phone_verified_at' => null,
                    'updated_at' => now(),
                ]);

            } else {
                $user->update([
                    'phone' => $inputs['phone'],
                    'updated_at' => now(),
                ]);
            }

            if (!empty($current_user->email)) {
                $password_reset_by_email = PasswordReset::where('email', $current_user->email)->first();
                $random_int_stringified = (string) random_int(1000000, 9999999);

                if ($password_reset_by_email != null) {
                    if (!empty($password_reset_by_email->phone)) {
                        if ($password_reset_by_email->phone != $inputs['phone']) {
                            $password_reset_by_email->update([
                                'phone' => $inputs['phone'],
                                'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, '0-9a-zA-Z'),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    if (empty($password_reset_by_email->phone)) {
                        $password_reset_by_email->update([
                            'phone' => $inputs['phone'],
                            'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, '0-9a-zA-Z'),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($password_reset_by_email == null) {
                    $password_reset_by_phone = PasswordReset::where('phone', $inputs['phone'])->first();

                    if ($password_reset_by_email == null) {
                        PasswordReset::create([
                            'email' => $current_user->email,
                            'phone' => $inputs['phone'],
                            'token' => $random_int_stringified,
                            'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, 'a-zA-Z'),
                        ]);
                    }
                }

            } else {
                $random_int_stringified = (string) random_int(1000000, 9999999);

                PasswordReset::create([
                    'phone' => $inputs['phone'],
                    'token' => $random_int_stringified,
                    'former_password' => $inputs['password'] != null ? $inputs['password'] : Random::generate(10, 'a-zA-Z'),
                ]);
            }
        }

        if ($inputs['username'] != null) {
            // Check if username already exists
            foreach ($users as $another_user):
                if (!empty($current_user->username)) {
                    if ($current_user->username != $inputs['username']) {
                        if ($another_user->username == $inputs['username']) {
                            return $this->handleError($inputs['username'], __('validation.custom.username.exists'), 400);
                        }
                    }
                }
            endforeach;

            $user->update([
                'username' => $inputs['username'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['email_verified_at'] != null) {
            $user->update([
                'email_verified_at' => $inputs['email_verified_at'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['phone_verified_at'] != null) {
            $user->update([
                'phone_verified_at' => $inputs['phone_verified_at'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['password'] != null) {
            if ($inputs['confirm_password'] != $inputs['password'] OR $inputs['confirm_password'] == null) {
                return $this->handleError($inputs['confirm_password'], __('notifications.confirm_password_error'), 400);
            }

            // if (preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $inputs['password']) == 0) {
            //     return $this->handleError($inputs['password'], __('miscellaneous.password.error'), 400);
            // }

            if (!empty($current_user->email)) {
                $password_reset = PasswordReset::where('email', $current_user->email)->first();
                $random_int_stringified = (string) random_int(1000000, 9999999);

                // If password_reset exists, update it
                if ($password_reset != null) {
                    $password_reset->update([
                        'token' => $random_int_stringified,
                        'former_password' => $inputs['password'],
                        'updated_at' => now(),
                    ]);
                }

            } else {
                if (!empty($current_user->phone)) {
                    $password_reset = PasswordReset::where('phone', $current_user->phone)->first();
                    $random_int_stringified = (string) random_int(1000000, 9999999);

                    // If password_reset exists, update it
                    if ($password_reset != null) {
                        $password_reset->update([
                            'token' => $random_int_stringified,
                            'former_password' => $inputs['password'],
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $user->update([
                'password' => Hash::make($inputs['password']),
                'updated_at' => now(),
            ]);
        }

        if ($inputs['email_frequency'] != null) {
            $user->update([
                'email_frequency' => $inputs['email_frequency'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['two_factor_secret'] != null) {
            $user->update([
                'two_factor_secret' => $inputs['two_factor_secret'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['two_factor_recovery_codes'] != null) {
            $user->update([
                'two_factor_recovery_codes' => $inputs['two_factor_recovery_codes'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['two_factor_confirmed_at'] != null) {
            $user->update([
                'two_factor_confirmed_at' => $inputs['two_factor_confirmed_at'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['two_factor_phone_confirmed_at'] != null) {
            $user->update([
                'two_factor_phone_confirmed_at' => $inputs['two_factor_phone_confirmed_at'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_incognito'] != null) {
            $user->update([
                'is_incognito' => $inputs['is_incognito'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['promo_code'] != null) {
            $user->update([
                'promo_code' => $inputs['promo_code'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['is_promoted'] != null) {
            $user->update([
                'is_promoted' => $inputs['is_promoted'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['country_id'] != null) {
            $user->update([
                'country_id' => $inputs['country_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['currency_id'] != null) {
            $user->update([
                'currency_id' => $inputs['currency_id'],
                'updated_at' => now(),
            ]);
        }

        if ($inputs['status_id'] != null) {
            $user->update([
                'status_id' => $inputs['status_id'],
                'updated_at' => now(),
            ]);
        }

        if ($request->role_id != null) {
            $user->roles()->syncWithoutDetaching([$request->role_id]);
        }

        if ($request->organization_id != null) {
            $user->organizations()->syncWithoutDetaching([$request->organization_id]);
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $password_reset_email = PasswordReset::whereNotNull('email')->where('email', $user->email)->first();
        $password_reset_phone = PasswordReset::whereNotNull('phone')->where('phone', $user->phone)->first();
        $personal_access_tokens = PersonalAccessToken::where('tokenable_id', $user->id)->get();
        $notifications = Notification::where('from_user_id', $user->id)->get();
        $directory = $_SERVER['DOCUMENT_ROOT'] . '/public/storage/images/users/' . $user->id;

        if (!is_null($personal_access_tokens)) {
            foreach ($personal_access_tokens as $personal_access_token):
                $personal_access_token->delete();
            endforeach;
        }

        if (!is_null($notifications)) {
            foreach ($notifications as $notification):
                $notification->delete();
            endforeach;
        }

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }

        if ($password_reset_email != null AND $password_reset_phone != null) {
            $password_reset_email->delete();

        } else {
            if ($password_reset_email == null AND $password_reset_phone != null) {
                $password_reset_phone->delete();
            }

            if ($password_reset_email != null AND $password_reset_phone == null) {
                $password_reset_email->delete();
            }
        }

        $user->delete();

        $users = User::orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.delete_user_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Find by "username"
     *
     * @param  string $username
     * @return \Illuminate\Http\Response
     */
    public function profile($username)
    {
        $user = User::where('username', $username)->first();

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.find_user_success'));
    }

    /**
     * Search (by filtering or not) a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // A more surgical search
        $search = trim($request->data);
        $keywords = preg_split('/\s+/', $search); // split by space
        // Request
        $query = User::query();

        // Apply the filter to the user firstname/lastname
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('firstname', 'LIKE', $keyword . '%')
                    ->orWhere('lastname', 'LIKE', $keyword . '%')
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", [$keyword . '%']);
                });
            }
        });

        // Add dynamic conditions
        $query->when($request->roles_ids, function ($query) use ($request) {
            return $query->whereHas('roles', function ($query) use ($request) {
                $query->whereIn('id', $request->roles_ids);
            });
        });

        $query->when($request->not_roles_ids, function ($query) use ($request) {
            return $query->whereDoesntHave('roles', function ($query) use ($request) {
                $query->whereIn('id', $request->not_roles_ids);
            });
        });

        // Tri par date de mise à jour et pagination
        $users = $query->orderByDesc('updated_at')->paginate(10);
        $count_users = $query->count();
        $message = ($count_users > 0 ? __('notifications.find_all_members_success') : __('notifications.find_member_404'));

        return $this->handleResponse(ResourcesUser::collection($users), $message, $users->lastPage(), $count_users);
    }

    /**
     * Search all users having a specific role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $role_name
     * @return \Illuminate\Http\Response
     */
    public function findByRole(Request $request, $role_name)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Requests
        $users = User::whereHas('roles', function ($query) use ($role_name) {
                                    $query->where('role_name', $role_name);
                                })->orderBy('firstname')->paginate(10);
        $partner = null;

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
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            // Otherwise, send all data
            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $users->total(), $partnerResource);
    }

    /**
     * Search all users having a role different than the given
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $role_name
     * @return \Illuminate\Http\Response
     */
    public function findByNotRole(Request $request, $role_name)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Requests
        $users = User::whereDoesntHave('roles', function ($query) use ($role_name) {
                                    $query->where('role_name' . $role_name);
                                })->orderBy('firstname')->paginate(10);
        $partner = null;

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
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            // Otherwise, send all data
            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $users->total(), $partnerResource);
    }

    /**
     * Search all users having a role different than the given
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function worksSubscribers(Request $request, $id)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Requests
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $users = User::whereHas('carts', function ($cartQuery) use ($user) {
                            $cartQuery->where('entity', 'consultation')
                            ->whereHas('works', function ($workQuery) use ($user) {
                                $workQuery->where('user_id', $user->id);
                            });
                        })->where('id', '<>', $user->id) // exclude current user
                        ->distinct()->paginate(10);
        $partner = null;

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
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            // Otherwise, send all data
            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $users->total(), $partnerResource);
    }

    /**
     * Retrieves users in an organization with a specific role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $organization_id
     * @param  string  $role_name
     * @return \Illuminate\Http\Response
     */
    public function organizationMembers(Request $request, $organization_id, $role_name)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Requests
        // Get the organization
        $organization = Organization::find($organization_id);

        if (is_null($organization)) {
            return $this->handleError(__('notifications.find_organization_404'));
        }

        // Creates the query to retrieve users with a specific role
        $usersQuery = $organization->users()->whereHas('roles', function ($query) use ($role_name) {
                                                    $query->where('role_name', $role_name);
                                                });
        // Executes the query to retrieve users
        $users = $usersQuery->orderBy('firstname')->paginate(10);
        $partner = null;

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
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            // Otherwise, send all data
            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $users->total(), $partnerResource);
    }

    /**
     * Retrieves users in a circle / event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $entity
     * @param  int  $entity_id
     * @return \Illuminate\Http\Response
     */
    public function groupMembers(Request $request, $entity, $entity_id)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Requests
        $users = null;
        $partner = null;

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $users = $circle->users()->paginate(10);
        }

        if ($entity == 'event') {
            $event = Event::find($entity_id);

            if (is_null($event)) {
                return $this->handleError(__('notifications.find_event_404'));
            }

            $users = $event->users()->paginate(10);
        }

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
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            // Otherwise, send all data
            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

        } else {
            $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                    $query->where('category_partner.status_id', $active_status->id);
                                })->where(function ($query) use ($users_ids) {
                                    $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                })->inRandomOrder()->first() : null;
        }

        $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'), $users->lastPage(), $users->total(), $partnerResource);
    }

    /**
     * Find all circles / events of a specific user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string $entity
     * @param  int $id
     * @param  int $status_id
     * @return \Illuminate\Http\Response
     */
    public function memberGroups($entity, $id, $status_id = null)
    {
        // Groups
        $partnership_status_group = Group::where('group_name', 'Etat du partenariat')->first();
        // Statuses
        $active_status = Status::where([['status_name->fr', 'Actif'], ['group_id', $partnership_status_group->id]])->first();
        // Get partners & sponsors IDs
        $users_ids = User::whereHas('roles', function ($query) { $query->where('role_name', 'Partenaire')->orWhere('role_name', 'Sponsor'); })->pluck('id')->toArray();
        // Request
        $partner = null;
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $status = Status::find($status_id);

        if (is_null($status)) {
            return $this->handleError(__('notifications.find_status_404'));
        }

        // Subscription
        $is_subscribed = $user->hasValidSubscription();

        if ($entity == 'circle') {
            $circles = $user->circles()->wherePivot('status_id', $status?->id)->orderByPivot('created_at', 'desc')->paginate(10);
            $count_circles = $user->circles()->wherePivot('status_id', $status?->id)->count();

            if ($is_subscribed) {
                $valid_subscription = $user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

            $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

            return $this->handleResponse(ResourcesCircle::collection($circles), __('notifications.find_all_circles_success'), $circles->lastPage(), $count_circles, $partnerResource);
        }

        if ($entity == 'event') {
            $events = $user->events()->wherePivot('status_id', $status?->id)->orderByPivot('created_at', 'desc')->paginate(10);
            $count_events = $user->events()->wherePivot('status_id', $status?->id)->count();

            if ($is_subscribed) {
                $valid_subscription = $user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

            $partnerResource = !empty($partner) ? new ResourcesPartner($partner) : null;

            return $this->handleResponse(ResourcesEvent::collection($events), __('notifications.find_all_events_success'), $events->lastPage(), $count_events, $partnerResource);
        }

        if ($entity == 'organization') {
            $organizations = $user->organizations()->orderByPivot('created_at', 'desc')->paginate(10);
            $count_organizations = $user->organizations()->count();

            if ($is_subscribed) {
                $valid_subscription = $user->validSubscriptions()->sortByDesc(function ($subscription) { return $subscription->pivot->created_at; })->first();
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($valid_subscription, $active_status) {
                                        $query->where('category_partner.category_id', $valid_subscription->category_id)->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;

            } else {
                $partner = Partner::whereHas('categories')->exists() ? Partner::whereHas('categories', function ($query) use ($active_status) {
                                        $query->where('category_partner.status_id', $active_status->id);
                                    })->where(function ($query) use ($users_ids) {
                                        $query->whereIn('from_user_id', $users_ids)->orWhereNotNull('from_organization_id');
                                    })->inRandomOrder()->first() : null;
            }

            return $this->handleResponse(ResourcesOrganization::collection($organizations), __('notifications.find_all_organizations_success'), $organizations->lastPage(), $count_organizations);
        }
    }

    /**
     * Check if user is circle admin or event speaker.
     *
     * @param  string $entity
     * @param  int $entity_id
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function isMainMember($entity, $entity_id, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        if ($entity == 'circle') {
            $circle = Circle::find($entity_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            $users = $circle->users()->wherePivot('is_admin', 1)->get();
            // Check user presence
            $isUserPresent = $users->contains('id', $user->id);

            if ($isUserPresent) {
                return $this->handleResponse(true, __('notifications.find_user_success'));

            } else {
                return $this->handleResponse(false, __('notifications.find_user_404'));
            }
        }

        if ($entity == 'event') {
            $event = Event::find($entity_id);

            if (is_null($event)) {
                return $this->handleError(__('notifications.find_event_404'));
            }

            $users = $event->users()->wherePivot('is_speaker', 1)->get();
            // Check user presence
            $isUserPresent = $users->contains('id', $user->id);

            if ($isUserPresent) {
                return $this->handleResponse(true, __('notifications.find_user_success'));

            } else {
                return $this->handleResponse(false, __('notifications.find_user_404'));
            }
        }
    }

    /**
     * Check if user is partner.
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function isPartner($user_id)
    {
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $hasPivotPartner = User::where('users.id', $user->id)->whereHas('roles', function ($query) {
                                    $query->where('roles.role_name', 'Partenaire');
                                })->exists();

        if ($hasPivotPartner) {
            return $this->handleResponse(true, __('notifications.find_user_success'), null);

        } else {
            return $this->handleResponse(false, __('notifications.find_user_404'), null);
        }
    }

    /**
     * Search all users having specific status.
     *
     * @param  int $status_id
     * @return \Illuminate\Http\Response
     */
    public function findByStatus($status_id)
    {
        $users = User::where('status_id', $status_id)->orderByDesc('created_at')->get();

        return $this->handleResponse(ResourcesUser::collection($users), __('notifications.find_all_users_success'));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Get inputs
        $inputs = [
            'username' => $request->username,
            'password' => $request->password
        ];

        if ($inputs['username'] == null OR $inputs['username'] == ' ') {
            return $this->handleError($inputs['username'], __('validation.required'), 400);
        }

        if ($inputs['password'] == null) {
            return $this->handleError($inputs['password'], __('validation.required'), 400);
        }

        if (is_numeric($inputs['username'])) {
            $user = User::where('phone', $inputs['username'])->first();

            if (!$user) {
                return $this->handleError($inputs['username'], __('auth.username'), 400);
            }

            if (!Hash::check($inputs['password'], $user->password)) {
                return $this->handleError($inputs['password'], __('auth.password'), 400);
            }

            // Check if phone is verified
            if ($user->phone_verified_at == null) {
                $password_reset = PasswordReset::where('phone', $user->phone)->first();
				$object = new stdClass();

				$object->password_reset = new ResourcesPasswordReset($password_reset);
				$object->user = new ResourcesUser($user);

                return $this->handleError($object, __('notifications.unverified_token_phone'), 400);
            }

            // Check if user is blocked
            $is_toxic = ToxicContent::where([['for_user_id', $user->id], ['is_unlocked', 0]])->exists();

            if ($is_toxic) {
                $toxic_content = ToxicContent::where([['for_user_id', $user->id], ['is_unlocked', 0]])->first();
                $object = new stdClass();

                $object->toxic_content = new ResourcesToxicContent($toxic_content);
                $object->user = new ResourcesUser($user);

                return $this->handleError($object, __('notifications.blocked_user'), 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $user->update([
                'api_token' => $token,
                'updated_at' => now(),
            ]);

            return $this->handleResponse(new ResourcesUser($user), __('notifications.login_user_success'));

        } else {
            $user = User::where('email', $inputs['username'])->orWhere('username', $inputs['username'])->first();

            if (!$user) {
                return $this->handleError($inputs['username'], __('auth.username'), 400);
            }

            if (!Hash::check($inputs['password'], $user->password)) {
                return $this->handleError($inputs['password'], __('auth.password'), 400);
            }

            // Check if email is verified
            if (!empty($user->email)) {
                if ($inputs['username'] == $user->email) {
                    if ($user->email_verified_at == null) {
                        $password_reset = PasswordReset::where('email', $user->email)->first();
                        $object = new stdClass();

                        $object->password_reset = new ResourcesPasswordReset($password_reset);
                        $object->user = new ResourcesUser($user);

                        return $this->handleError($object, __('notifications.unverified_token_email'), 400);
                    }
                }
            }

            // Check if user is blocked
            $is_toxic = ToxicContent::where([['for_user_id', $user->id], ['is_unlocked', 0]])->exists();

            if ($is_toxic) {
                $toxic_content = ToxicContent::where([['for_user_id', $user->id], ['is_unlocked', 0]])->first();
                $object = new stdClass();

                $object->toxic_content = new ResourcesToxicContent($toxic_content);
                $object->user = new ResourcesUser($user);

                return $this->handleError($object, __('notifications.blocked_user'), 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $user->update([
                'api_token' => $token,
                'updated_at' => now(),
            ]);

            return $this->handleResponse(new ResourcesUser($user), __('notifications.login_user_success'));
        }
    }

    /**
     * Switch between user statuses.
     *
     * @param  $id
     * @param  $status_id
     * @return \Illuminate\Http\Response
     */
    public function switchStatus($id, $status_id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        /*
            HISTORY AND/OR NOTIFICATION MANAGEMENT
        */
        $status_activated = Status::where('status_name->fr', 'Activé')->first();
        $status_disabled = Status::where('status_name->fr', 'Désactivé')->first();
        $status_blocked = Status::where('status_name->fr', 'Bloqué')->first();
        $status_unread = Status::where('status_name->fr', 'Non lue')->first();
        $type_user_return = Type::where('type_name->fr', 'Utilisateur de retour')->first();

        if ($status_id == $status_activated->id) {
            Notification::create([
                'type_id' => $type_user_return->id,
                'status_id' => $status_unread->id,
                'to_user_id' => $user->id,
            ]);

            // update "status_id" column
            $user->update([
                'status_id' => $status_activated->id,
                'updated_at' => now()
            ]);
        }

        if ($status_id == $status_disabled->id) {
            // update "status_id" column
            $user->update([
                'status_id' => $status_disabled->id,
                'updated_at' => now()
            ]);
        }

        if ($status_id == $status_blocked->id) {
            // update "status_id" column
            $user->update([
                'status_id' => $status_blocked->id,
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update user role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $action
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request, $action, $id)
    {
        $user = User::find($id);

        if ($action == 'add') {
            $user->roles()->syncWithoutDetaching([$request->role_id]);
        }

        if ($action == 'remove') {
            $user->roles()->detach([$request->role_id]);
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update user membership in group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $entity
     * @param  int  $entity_id
     * @param  string  $action
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateUserMembership(Request $request, $entity, $entity_id, $action, $id)
    {
        // Groups
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $event_status_group = Group::where('group_name', 'Etat de l\'événement')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Status
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Type
        $invitation_as_speaker_type = Type::where([['type_name->fr', 'Invitation en tant que speaker'], ['group_id', $notification_type_group->id]])->first();
        $invitation_as_admin_type = Type::where([['type_name->fr', 'Invitation en tant qu\'administrateur'], ['group_id', $notification_type_group->id]])->first();
        $invitation_type = Type::where([['type_name->fr', 'Invitation'], ['group_id', $notification_type_group->id]])->first();
        // $request_for_membership_type = Type::where([['type_name->fr', 'Demande d\'adhésion'], ['group_id', $notification_type_group->id]])->first();
        // Requests
        $user = User::find($id);

        if ($entity != 'organization' AND $entity != 'event' AND $entity != 'circle') {
            return $this->handleError(__('validation.custom.owner.required') . ' (organization / event / circle)');
        }

        if ($action != 'add' AND $action != 'remove') {
            return $this->handleError(__('validation.custom.action.required') . ' (add / remove)');
        }

        if ($action == 'add') {
            if ($entity == 'organization') {
                $organization = Event::find($entity_id);

                if (is_null($organization)) {
                    return $this->handleError(__('notifications.find_organization_404'));
                }

                $user->organizations()->syncWithoutDetaching([$organization->id]);
            }

            if ($entity == 'event') {
                $event = Event::find($entity_id);

                if (is_null($event)) {
                    return $this->handleError(__('notifications.find_event_404'));
                }

                // $user->events()->syncWithoutDetaching([$event->id => ['is_speaker' => $request->is_speaker, 'status_id' => $request->status_id]]);
                // Créer un tableau vide pour les relations à synchroniser
                $syncData = [$event->id => []];

                // Ajouter 'is_speaker' si envoyé dans la requête
                if (!empty($request->is_speaker)) {
                    $syncData[$event->id]['is_speaker'] = $request->is_speaker;

                    if ($request->is_speaker == 1) {
                        Notification::create([
                            'type_id' => $invitation_as_speaker_type->id,
                            'status_id' => $unread_notification_status->id,
                            'from_user_id' => $event->organization?->user_id,
                            'to_user_id' => $user->id,
                            'event_id' => $event->id,
                        ]);

                    } else {
                        Notification::create([
                            'type_id' => $invitation_type->id,
                            'status_id' => $unread_notification_status->id,
                            'from_user_id' => $event->organization?->user_id,
                            'to_user_id' => $user->id,
                            'event_id' => $event->id,
                        ]);
                    }
                }

                // Ajouter 'status_id' si envoyé dans la requête
                if (!empty($request->status_id)) {
                    $syncData[$event->id]['status_id'] = $request->status_id;
                }

                // Synchroniser sans détacher
                $user->events()->syncWithoutDetaching($syncData);
            }

            if ($entity == 'circle') {
                $circle = Circle::find($entity_id);

                if (is_null($circle)) {
                    return $this->handleError(__('notifications.find_circle_404'));
                }

                // $user->circles()->syncWithoutDetaching([$circle->id => ['is_admin' => $request->is_admin, 'status_id' => $request->status_id]]);
                // Créer un tableau vide pour les relations à synchroniser
                $syncData = [$circle->id => []];

                // Ajouter 'is_admin' si envoyé dans la requête
                if (!empty($request->is_admin)) {
                    $syncData[$circle->id]['is_admin'] = $request->is_admin;

                    if ($request->is_admin == 1) {
                        Notification::create([
                            'type_id' => $invitation_as_admin_type->id,
                            'status_id' => $unread_notification_status->id,
                            'from_user_id' => $circle->user_id,
                            'to_user_id' => $user->id,
                            'circle_id' => $circle->id,
                        ]);

                    } else {
                        Notification::create([
                            'type_id' => $invitation_type->id,
                            'status_id' => $unread_notification_status->id,
                            'from_user_id' => $circle->user_id,
                            'to_user_id' => $user->id,
                            'circle_id' => $circle->id,
                        ]);
                    }
                }

                // Ajouter 'status_id' si envoyé dans la requête
                if (!empty($request->status_id)) {
                    $syncData[$circle->id]['status_id'] = $request->status_id;
                }

                // Synchroniser sans détacher
                $user->circles()->syncWithoutDetaching($syncData);
            }
        }

        if ($action == 'remove') {
            if ($entity == 'event') {
                $event = Event::find($entity_id);

                if (is_null($event)) {
                    return $this->handleError(__('notifications.find_event_404'));
                }

                $user->events()->detach([$event->id]);

                $notification = Notification::where('to_user_id', $user->id)->where('event_id', $event->id)->first();

                if (!empty($notification)) {
                    $notification->delete();
                }
            }

            if ($entity == 'circle') {
                $circle = Event::find($entity_id);

                if (is_null($circle)) {
                    return $this->handleError(__('notifications.find_circle_404'));
                }

                $user->circles()->detach([$circle->id]);

                $notification = Notification::where('to_user_id', $user->id)->where('circle_id', $circle->id)->first();

                if (!empty($notification)) {
                    $notification->delete();
                }
            }
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }

    /**
     * Update user password in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, $id)
    {
        // Get inputs
        $inputs = [
            'former_password' => $request->former_password,
            'new_password' => $request->new_password,
            'confirm_new_password' => $request->confirm_new_password
        ];
        $user = User::find($id);

        if ($inputs['former_password'] == null) {
            return $this->handleError($inputs['former_password'], __('validation.custom.former_password.empty'), 400);
        }

        if ($inputs['new_password'] == null) {
            return $this->handleError($inputs['new_password'], __('validation.custom.new_password.empty'), 400);
        }

        if ($inputs['confirm_new_password'] == null) {
            return $this->handleError($inputs['confirm_new_password'], __('notifications.confirm_new_password'), 400);
        }

        if (Hash::check($inputs['former_password'], $user->password) == false) {
            return $this->handleError($inputs['former_password'], __('auth.password'), 400);
        }

        if ($inputs['confirm_new_password'] != $inputs['new_password']) {
            return $this->handleError($inputs['confirm_new_password'], __('notifications.confirm_new_password'), 400);
        }

        // if (preg_match('#^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$#', $inputs['new_password']) == 0) {
        //     return $this->handleError($inputs['new_password'], __('validation.custom.new_password.incorrect'), 400);
        // }

        // Update password reset
        if (!empty($user->email) AND !empty($user->phone)) {
            $password_reset = PasswordReset::where([['email', $user->email], ['phone', $user->phone]])->first();
            $random_int_stringified = (string) random_int(1000000, 9999999);

            // If password_reset doesn't exist, create it.
            if ($password_reset == null) {
                PasswordReset::create([
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'token' => $random_int_stringified,
                    'former_password' => $inputs['new_password'],
                ]);
            }

            // If password_reset exists, update it
            if ($password_reset != null) {
                $password_reset->update([
                    'token' => $random_int_stringified,
                    'former_password' => $inputs['new_password'],
                    'updated_at' => now(),
                ]);
            }

        } else {
            if (!empty($user->email)) {
                $password_reset = PasswordReset::where('email', $user->email)->first();
                $random_int_stringified = (string) random_int(1000000, 9999999);

                // If password_reset doesn't exist, create it.
                if ($password_reset == null) {
                    PasswordReset::create([
                        'email' => $user->email,
                        'token' => $random_int_stringified,
                        'former_password' => $inputs['new_password'],
                    ]);
                }

                // If password_reset exists, update it
                if ($password_reset != null) {
                    $password_reset->update([
                        'token' => $random_int_stringified,
                        'former_password' => $inputs['new_password'],
                        'updated_at' => now(),
                    ]);
                }
            }

            if (!empty($user->phone)) {
                $password_reset = PasswordReset::where('phone', $user->phone)->first();
                $random_int_stringified = (string) random_int(1000000, 9999999);

                // If password_reset doesn't exist, create it.
                if ($password_reset == null) {
                    PasswordReset::create([
                        'phone' => $user->phone,
                        'token' => $random_int_stringified,
                        'former_password' => $inputs['new_password'],
                    ]);
                }

                // If password_reset exists, update it
                if ($password_reset != null) {
                    $password_reset->update([
                        'token' => $random_int_stringified,
                        'former_password' => $inputs['new_password'],
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // update "password" and "password_visible" column
        $user->update([
            'password' => Hash::make($inputs['new_password']),
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_password_success'));
    }

    /**
     * Ask subscription to an event or a talk circle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  int  $addressee_id
     * @return \Illuminate\Http\Response
     */
    public function subscribeToGroup(Request $request, $id, $addressee_id)
    {
        // Groups
        $invitation_status_group = Group::where('group_name', 'Etat de l\'invitation')->first();
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $access_type_group = Group::where('group_name', 'Type d\'accès')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Statuses
        $on_hold_status = Status::where([['status_name->fr', 'En attente'], ['group_id', $invitation_status_group->id]])->first();
        $accepted_status = Status::where([['status_name->fr', 'Acceptée'], ['group_id', $invitation_status_group->id]])->first();
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $public_type = Type::where([['type_name->fr', 'Public'], ['group_id' => $access_type_group->id]])->first();
        $private_type = Type::where([['type_name->fr', 'Privé'], ['group_id' => $access_type_group->id]])->first();
        $invitation_type = Type::where([['type_name->fr', 'Invitation'], ['group_id' => $notification_type_group->id]])->first();
        $membership_request_type = Type::where([['type_name->fr', 'Demande d\'adhésion'], ['group_id' => $notification_type_group->id]])->first();
        // Requests
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $addressee = User::find($addressee_id);

        if (is_null($addressee)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        if (isset($request->circle_id)) {
            $circle = Circle::find($request->circle_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.find_circle_404'));
            }

            // If it's the current user who subscribed
            if ($user->id == $addressee->id) {
                // If the circle is public, accept the user
                if ($circle->type_id == $public_type->id) {
                    $circle->users()->attach($user->id, [
                        'status_id' => $accepted_status->id
                    ]);
                }

                // If the circle is private, put the user on hold
                if ($circle->type_id == $private_type->id) {
                    $circle->users()->attach($user->id, [
                        'status_id' => $on_hold_status->id
                    ]);
                }

                $admin_users = $circle->users()->where('is_admin', 1)->get();

                /*
                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                */
                foreach ($admin_users as $admin) {
                    Notification::create([
                        'type_id' => $membership_request_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $user->id,
                        'to_user_id' => $admin->pivot->user_id,
                        'circle_id' => $circle->id,
                    ]);
                }
            }

            // If it's a circle member who sent an invitation to another member
            if ($user->id != $addressee->id) {
                $circle->users()->attach($addressee->id, ['status_id' => $accepted_status->id]);

                /*
                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                */
                Notification::create([
                    'type_id' => $invitation_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $addressee->id,
                    'circle_id' => $circle->id
                ]);
            }
        }

        if (isset($request->event_id)) {
            $event = Event::find($request->event_id);

            if (is_null($event)) {
                return $this->handleError(__('notifications.find_event_404'));
            }

            // If it's the current user who subscribed
            if ($user->id == $addressee->id) {
                // If the event is public, accept the user
                if ($event->type_id == $public_type->id) {
                    $event->users()->attach($user->id, [
                        'status_id' => $accepted_status->id
                    ]);
                }

                // If the event is private, put the user on hold
                if ($event->type_id == $private_type->id) {
                    $event->users()->attach($user->id, [
                        'status_id' => $on_hold_status->id
                    ]);
                }

                /*
                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                */
                Notification::create([
                    'type_id' => $membership_request_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $event->user_id,
                    'event_id' => $event->id
                ]);
            }

            // If it's a event member who sent an invitation to another member
            if ($user->id != $addressee->id) {
                $event->users()->attach($addressee->id, ['status_id' => $accepted_status->id]);

                /*
                    HISTORY AND/OR NOTIFICATION MANAGEMENT
                */
                Notification::create([
                    'type_id' => $invitation_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $addressee->id,
                    'event_id' => $event->id
                ]);
            }
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.subscribe_user_success'));
    }

    /**
     * Unsubscribe to an event or a talk circle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  int  $addressee_id
     * @return \Illuminate\Http\Response
     */
    public function unsubscribeToGroup(Request $request, $id, $addressee_id)
    {
        // Groups
        $notification_status_group = Group::where('group_name', 'Etat de la notification')->first();
        $notification_type_group = Group::where('group_name', 'Type de notification')->first();
        // Statuses
        $unread_notification_status = Status::where([['status_name->fr', 'Non lue'], ['group_id', $notification_status_group->id]])->first();
        // Types
        $separation_type = Type::where([['type_name->fr', 'Séparation'], ['group_id', $notification_type_group->id]])->first();
        $expulsion_type = Type::where([['type_name->fr', 'Expulsion'], ['group_id', $notification_type_group->id]])->first();
        // Requests
        $user = User::find($id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $addressee = User::find($addressee_id);

        if (is_null($addressee)) {
            return $this->handleError(__('notifications.find_addressee_404'));
        }

        if (isset($request->circle_id)) {
            $circle = Circle::find($request->circle_id);

            if (is_null($circle)) {
                return $this->handleError(__('notifications.circle_404'));
            }

            $circle->users()->detach([$user->id]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // If it's the current user who has left
            if ($user->id == $addressee_id) {
                foreach ($circle->users as $member) {
                    Notification::create([
                        'type_id' => $separation_type->id,
                        'status_id' => $unread_notification_status->id,
                        'from_user_id' => $user->id,
                        'to_user_id' => $member->id,
                        'circle_id' => $circle->id,
                    ]);
                }
            }

            // If it's a circle admin who has withdrawn the current user
            if ($user->id != $addressee_id) {
                Notification::create([
                    'type_id' => $expulsion_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $addressee->id,
                    'circle_id' => $circle->id
                ]);
            }
        }

        if (isset($request->event_id)) {
            $event = Event::find($request->event_id);

            if (is_null($event)) {
                return $this->handleError(__('notifications.event_404'));
            }

            $event->users()->detach([$user->id]);

            /*
                HISTORY AND/OR NOTIFICATION MANAGEMENT
            */
            // If it's the current user who has left
            if ($user->id == $addressee_id) {
                Notification::create([
                    'type_id' => $separation_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $event->user_id,
                    'event_id' => $event->id
                ]);
            }

            // If it's a event member who has withdrawn the current user
            if ($user->id != $addressee_id) {
                Notification::create([
                    'type_id' => $expulsion_type->id,
                    'status_id' => $unread_notification_status->id,
                    'from_user_id' => $user->id,
                    'to_user_id' => $addressee->id,
                    'event_id' => $event->id
                ]);
            }
        }

        return $this->handleResponse(new ResourcesUser($user), __('notifications.unsubscribe_user_success'));
    }

    /**
     * Update user avatar picture in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAvatarPicture(Request $request, $id)
    {
        $inputs = [
            'user_id' => $request->user_id,
            'image_64' => $request->image_64
        ];
        // $extension = explode('/', explode(':', substr($inputs['image_64'], 0, strpos($inputs['image_64'], ';')))[1])[1];
        $replace = substr($inputs['image_64'], 0, strpos($inputs['image_64'], ',') + 1);
        // Find substring from replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $inputs['image_64']);
        $image = str_replace(' ', '+', $image);

        // Clean "avatars" directory
        $file = new Filesystem;
        $file->cleanDirectory($_SERVER['DOCUMENT_ROOT'] . '/public/storage/images/users/' . $inputs['user_id'] . '/avatar');
        // Create image URL
		$image_path = 'images/users/' . $id . '/avatar/' . Str::random(50) . '.png';

		// Upload image
		Storage::disk('public')->put($image_path, base64_decode($image));

		$user = User::find($id);

        $user->update([
            'avatar_url' => Storage::url($image_path),
            'updated_at' => now()
        ]);

        return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
    }
}
