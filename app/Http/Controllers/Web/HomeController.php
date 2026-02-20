<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class HomeController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
    }

    // ==================================== HTTP GET METHODS ====================================
    /**
     * GET: Change language
     *
     * @param  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLanguage($locale)
    {
        app()->setLocale($locale);
        session()->put('locale', $locale);

        return redirect()->back();
    }

    /**
     * GET: Welcome/Home page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->has('admin')) {
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

        } else {
            return view('welcome');
        }
    }

    /**
     * GET: About page
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        $titles = Lang::get('miscellaneous.public.about.content.titles');

        return view('about', ['titles' => $titles]);
    }

    /**
     * GET: About, inner pages
     *
     * @param  int entity
     * @return \Illuminate\View\View
     */
    public function aboutEntity($entity)
    {
        $titles = Lang::get('miscellaneous.public.about.' . $entity . '.titles');

        return view('about', [
            'titles' => $titles,
            'entity' => $entity,
            'entity_title' => __('miscellaneous.public.about.' . $entity . '.title'),
            'entity_description' => __('miscellaneous.public.about.' . $entity . '.description'),
            'entity_menu' => __('miscellaneous.menu.' . $entity),
        ]);
    }

    /**
     * Display the subscription form.
     *
     * @return \Illuminate\View\View
     */
    public function subscribe()
    {
        $subscription = Subscription::find(request()->get('subscription_id'));
        $subscription_type = Type::find($subscription->type_id);

        return view('subscribe-test', [
            'subscription' => $subscription,
            'subscription_type' => $subscription_type->getTranslation('type_name', str_replace('_', '-', app()->getLocale())),
        ]);
    }

    /**
     * Display the message about transaction in waiting.
     *
     * @return \Illuminate\View\View
     */
    public function transactionWaiting()
    {
        return view('transaction_message');
    }

    /**
     * Display the message about transaction done.
     *
     * @return \Illuminate\View\View
     */
    public function transactionMessage($order_number, $user_id)
    {
        if (is_numeric($user_id)) {
            // Find payment by order number and user ID API
            $payment2 = $this::$api_client_manager::call('GET', getApiURL() . '/payment/find_by_order_number_user/' . $order_number . '/' . $user_id);

            return view('transaction_message', [
                'message_content' => __('notifications.transaction_done'),
                'status_code' => (string) $payment2->data->status->id,
                'payment' => $payment2->data,
            ]);

        } else {
            // Find payment by order number API
            $payment1 = $this::$api_client_manager::call('GET', getApiURL() . '/payment/find_by_order_number/' . $order_number);

            return view('transaction_message', [
                'message_content' => __('notifications.transaction_done'),
                'status_code' => (string) $payment1->data->status->id,
                'payment' => $payment1->data,
            ]);
        }
    }

    /**
     * GET: Current user account
     *
     * @param $amount
     * @param $currency
     * @param $code
     * @param $user_id
     * @return \Illuminate\View\View
     */
    public function subscribed($amount = null, $currency = null, $code, $user_id)
    {
        // Find status by name API
        $failed_status_name = 'Echoué';
        $failed_status = $this::$api_client_manager::call('GET', getApiURL() . '/status/search/fr/' . $failed_status_name);

        if ($code == '0') {
            return view('transaction_message', [
                'status_code' => $code,
                'message_content' => __('notifications.processing_succeed')
            ]);
        }

        if ($code == '1') {
            // Find payment by order number API
            $payment = $this::$api_client_manager::call('GET', getApiURL() . '/payment/find_by_order_number/' . Session::get('order_number'));

            if ($payment->success) {
                // Update payment status API
                $this::$api_client_manager::call('PUT', getApiURL() . '/payment/switch_status/' . $payment->data->id . '/' . $failed_status->data->id);
            }

            return view('transaction_message', [
                'status_code' => $code,
                'message_content' => __('notifications.process_canceled')
            ]);
        }

        if ($code == '2') {
            // Find payment by order number API
            $payment = $this::$api_client_manager::call('GET', getApiURL() . '/payment/find_by_order_number/' . Session::get('order_number'));

            if ($payment->success) {
                // Update payment status API
                $this::$api_client_manager::call('PUT', getApiURL() . '/payment/switch_status/' . $payment->data->id . '/' . $failed_status->data->id);
            }

            return view('transaction_message', [
                'status_code' => $code,
                'message_content' => __('notifications.process_failed')
            ]);
        }
    }

    // ==================================== HTTP POST METHODS ====================================
    /**
     * POST: Download app
     *
     * @param  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function download()
    {
        $path = public_path('assets/apps/boongo-release-0_0_2.apk');

        if (! file_exists($path)) {
             return redirect()->back()->with('error_message', 'Fichier non trouvé.');
        }

        return response()->download($path,'Boongo.apk');
    }

    /**
     * POST: Register subscription
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function runSubscribe(Request $request)
    {
        $inputs = [
            'subscription_id' => $request->subscription_id,
            'transaction_type_id' => $request->transaction_type_id,
            'other_phone' => $request->other_phone_code . $request->other_phone_number,
            'app_url' => $request->app_url,
            'user_id' => $request->user_id
        ];
        // Find type by name API
        // -- MOBILE MONEY
        $mobile_money_type_name = 'Mobile money';
        $mobile_money_type = $this::$api_client_manager::call('GET', getApiURL() . '/type/search/fr/' . $mobile_money_type_name);
        // -- BANK CARD
        $bank_card_type_name = 'Carte bancaire';
        $bank_card_type = $this::$api_client_manager::call('GET', getApiURL() . '/type/search/fr/' . $bank_card_type_name);

        if ($inputs['transaction_type_id'] == null) {
            return redirect()->back()->with('error_message', __('notifications.transaction_type_error'));
        }

        if ($inputs['transaction_type_id'] != null) {
            if ($inputs['transaction_type_id'] == $mobile_money_type->data->id) {
                if ($request->other_phone_code == null or $request->other_phone_number == null) {
                    return redirect()->back()->with('error_message', __('validation.custom.phone.incorrect'));
                }

                $cart = $this::$api_client_manager::call('POST', getApiURL() . '/cart/purchase/' . $inputs['user_id'], $request->api_token, $inputs);

                if ($cart->success) {
                    return redirect()->route('transaction.waiting', [
                        'app_id' => '-',
                        'success_message' => $cart->data->result_response->order_number . '-' . $inputs['user_id'],
                    ]);

                } else {
                    return redirect()->back()->with('error_message', $cart->message);
                }
            }

            if ($inputs['transaction_type_id'] == $bank_card_type->data->id) {
                $cart = $this::$api_client_manager::call('POST', getApiURL() . '/cart/purchase/' . $inputs['user_id'], $request->api_token, $inputs);

                if ($cart->success) {
                    return redirect($cart->data->result_response->url)->with('order_number', $cart->data->result_response->order_number);

                } else {
                    return redirect()->back()->with('error_message', $cart->message);
                }
            }
        }
    }
}
