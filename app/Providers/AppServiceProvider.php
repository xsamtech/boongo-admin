<?php

namespace App\Providers;

use App\Models\Country;
use App\Models\Group;
use App\Models\Type;
use Illuminate\Support\ServiceProvider;
use App\Http\Resources\Country as ResourcesCountry;
use App\Http\Resources\Type as ResourcesType;
use App\Http\Resources\User as ResourcesUser;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(NotificationService $notificationService): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Select all countries
        $countries = Country::all();
        // Select all types by group (Type de transaction)
        $group = Group::where('group_name', 'Type de paiement')->first();
        $transaction_types = !empty($group) ? Type::where('group_id', $group->id)->get() : Type::all();

        view()->composer('*', function ($view) use ($countries, $transaction_types, $notificationService) {
            $current_user = null;

            if (Auth::check()) {
                $current_user = new ResourcesUser(Auth::user());
                // $unread_notifications = $notificationService->getUserNotifications($current_user->id);

                $view->with('current_user', $current_user);
                // $view->with('unread_notifications', $unread_notifications['unread']);
            }

            $view->with('current_locale', app()->getLocale());
            $view->with('available_locales', config('app.available_locales'));
            $view->with('countries', ResourcesCountry::collection($countries)->toArray(request()));
            $view->with('transaction_types', ResourcesType::collection($transaction_types)->toArray(request()));
        });
    }
}
