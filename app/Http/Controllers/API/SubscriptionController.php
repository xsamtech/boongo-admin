<?php

namespace App\Http\Controllers\API;

use App\Models\Group;
use App\Models\Payment;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Subscription as ResourcesSubscription;
use App\Http\Resources\User as ResourcesUser;
use Carbon\Carbon;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class SubscriptionController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Récupérer tous les abonnements avec leurs catégories
        $subscriptions = Subscription::with('category')->get();

        // Regrouper les abonnements par catégorie
        $groupedSubscriptions = $subscriptions->groupBy(function ($subscription) {
            return $subscription->category->category_name;
        });

        // Transformer les données regroupées pour les envoyer dans la réponse
        // Ici on fait une ressource personnalisée pour chaque catégorie, tu peux adapter selon ce que tu veux retourner
        $groupedSubscriptionsResource = $groupedSubscriptions->map(function ($group) {
            return ResourcesSubscription::collection($group); // ou créer une nouvelle ressource personnalisée pour chaque groupe
        });

        return $this->handleResponse($groupedSubscriptionsResource, __('notifications.find_all_subscriptions_success'));
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
            'number_of_hours' => $request->number_of_hours,
            'price' => $request->price,
            'currency_id' => $request->currency_id,
            'type_id' => $request->type_id,
            'category_id' => $request->category_id
        ];

        $validator = Validator::make($inputs, [
            'number_of_hours' => ['required'],
            'price' => ['required'],
            'currency_id' => ['required'],
            'type_id' => ['required'],
            'category_id' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->handleError($validator->errors());       
        }

        $subscription = Subscription::create($inputs);

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.create_subscription_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subscription = Subscription::find($id);

        if (is_null($subscription)) {
            return $this->handleError(__('notifications.find_subscription_404'));
        }

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.find_subscription_success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subscription $subscription)
    {
        // Get inputs
        $inputs = [
            'number_of_hours' => $request->number_of_hours,
            'price' => $request->price,
            'currency_id' => $request->currency_id,
            'type_id' => $request->type_id,
            'category_id' => $request->category_id
        ];

        if ($inputs['number_of_hours'] != null) {
            $subscription->update([
                'number_of_hours' => $inputs['number_of_hours'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['price'] != null) {
            $subscription->update([
                'price' => $inputs['price'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['currency_id'] != null) {
            $subscription->update([
                'currency_id' => $inputs['currency_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['type_id'] != null) {
            $subscription->update([
                'type_id' => $inputs['type_id'],
                'updated_at' => now()
            ]);
        }

        if ($inputs['category_id'] != null) {
            $subscription->update([
                'category_id' => $inputs['category_id'],
                'updated_at' => now()
            ]);
        }

        return $this->handleResponse(new ResourcesSubscription($subscription), __('notifications.update_subscription_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Subscription  $subscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        $subscriptions = Subscription::all();

        return $this->handleResponse(ResourcesSubscription::collection($subscriptions), __('notifications.delete_subscription_success'));
    }

    // ==================================== CUSTOM METHODS ====================================
    /**
     * Check if user is subscribed
     *
     * @param  int $user_id
     * @return \Illuminate\Http\Response
     */
    public function isSubscribed($user_id)
    {
        // Group
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $valid_status = Status::where([['status_name->fr', 'Valide'], ['group_id', $subscription_status_group->id]])->first();
        // Request
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $hasPivotValid = User::whereHas('subscriptions', function ($q) use ($user, $valid_status) {
                        $q->where('subscription_user.user_id', $user->id)
                            ->where('subscription_user.status_id', $valid_status->id);
                    })->exists();

        if ($hasPivotValid) {
            return $this->handleResponse(1, __('notifications.find_user_success'), null);

        } else {
            return $this->handleResponse(0, __('notifications.find_user_404'), null);
        }
    }

    /**
     * Validate a user subscriptions.
     *
     * @param  int $user_id
     */
    public function validateSubscription($user_id)
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

        $last_subscription_cart = $user->carts()->where([['entity', 'subscription'], ['status_id', $paid_status->id]])->latest()->first();

        if (!$last_subscription_cart) {
            return $this->handleError(__('notifications.find_subscription_404'));
        }

        $cart_payment = Payment::find($last_subscription_cart->payment_id);

        if (is_null($cart_payment)) {
            return $this->handleError(__('notifications.find_payment_404'));
        }

        if ($cart_payment->status_id == $done_status->id) {
            $subscriptionsIds = $last_subscription_cart->subscriptions()->pluck('subscriptions.id')->toArray();

            // Update all subscriptions linked to this cart in the pivot table "cart_subscription"
            foreach ($subscriptionsIds as $id) {
                $last_subscription_cart->subscriptions()->updateExistingPivot($id, [
                    'status_id' => $valid_status->id // We update the "status_id" in the pivot
                ]);
            }

            return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));

        } else {
            return $this->handleResponse(new ResourcesUser($user), __('notifications.find_done_payment_404'));
        }
    }

    /**
     * Invalidate a user subscription.
     *
     * @param  int $user_id
     */
    public function invalidateSubscription($user_id)
    {
        // Groups
        $cart_status_group = Group::where('group_name', 'Etat du panier')->first();
        $subscription_status_group = Group::where('group_name', 'Etat de l\'abonnement')->first();
        // Status
        $paid_status = Status::where([['status_name->fr', 'Payé'], ['group_id', $cart_status_group->id]])->first();
        $expired_status = Status::where([['status_name->fr', 'Expiré'], ['group_id', $subscription_status_group->id]])->first();
        // Requests
        $user = User::find($user_id);

        if (is_null($user)) {
            return $this->handleError(__('notifications.find_user_404'));
        }

        $last_subscription_cart = $user->carts()->where([['entity', 'subscription'], ['status_id', $paid_status->id]])->latest()->first();

        if (is_null($last_subscription_cart)) {
            return $this->handleError(__('notifications.find_cart_404'));
        }

        $valid_subscription = $user->validSubscriptions()->first();

        if ($valid_subscription != null) {
            $subscription = Subscription::find($valid_subscription->subscription_id);

            if (is_null($subscription)) {
                return $this->handleError(__('notifications.find_subscription_404'));
            }

            // Create two date instances
            $current_date_instance = Carbon::parse(date('Y-m-d h:i:s'));
            $subscription_date_instance = Carbon::parse($valid_subscription->created_at);
            // Determine the difference between dates
            $diff = $current_date_instance->diff($subscription_date_instance);
            $diffInHours = $diff->days * 24 + $diff->h + $diff->i / 60;

            if (($subscription->number_of_hours - round($diffInHours)) > 0) {
                return $this->handleResponse(new ResourcesUser($user), __('notifications.invalidate_subscription_failed') . ' (Time remaining: '. ($subscription->number_of_hours - round($diffInHours)) .')');

            } else {
                $last_subscription_cart->subscriptions()->updateExistingPivot($valid_subscription->id, ['status_id' => $expired_status->id]);

                return $this->handleResponse(new ResourcesUser($user), __('notifications.update_user_success'));
            }

        } else {
            return $this->handleError(new ResourcesUser($user), __('notifications.find_subscription_404'), 404);
        }
    }
}
