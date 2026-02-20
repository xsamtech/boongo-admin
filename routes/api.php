<?php
/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Default API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'localization'])->group(function () {
    Route::apiResource('country', 'App\Http\Controllers\API\CountryController')->except(['index', 'store', 'search']);
    Route::apiResource('currency', 'App\Http\Controllers\API\CurrencyController')->except(['index', 'store', 'search']);
    Route::apiResource('currencies_rate', 'App\Http\Controllers\API\CurrenciesRateController')->except(['store', 'findCurrencyRate']);
    Route::apiResource('group', 'App\Http\Controllers\API\GroupController');
    Route::apiResource('status', 'App\Http\Controllers\API\StatusController')->except(['search', 'findByGroup']);
    Route::apiResource('type', 'App\Http\Controllers\API\TypeController')->except(['search', 'findByGroup']);
    Route::apiResource('category', 'App\Http\Controllers\API\CategoryController')->except(['search', 'findByGroup', 'allUsedInWorks', 'allUsedInWorksType']);
    Route::apiResource('report_reason', 'App\Http\Controllers\API\ReportReasonController')->except(['store', 'findByEntity']);
    Route::apiResource('work', 'App\Http\Controllers\API\WorkController');
    Route::apiResource('like', 'App\Http\Controllers\API\LikeController');
    Route::apiResource('file', 'App\Http\Controllers\API\FileController');
    Route::apiResource('subscription', 'App\Http\Controllers\API\SubscriptionController')->except(['index']);
    Route::apiResource('cart', 'App\Http\Controllers\API\CartController');
    Route::apiResource('partner', 'App\Http\Controllers\API\PartnerController');
    Route::apiResource('activation_code', 'App\Http\Controllers\API\ActivationCodeController');
    Route::apiResource('role', 'App\Http\Controllers\API\RoleController')->except(['search']);
    Route::apiResource('user', 'App\Http\Controllers\API\UserController')->except(['store', 'show', 'login']);
    Route::apiResource('organization', 'App\Http\Controllers\API\OrganizationController');
    Route::apiResource('course_year', 'App\Http\Controllers\API\CourseYearController');
    Route::apiResource('program', 'App\Http\Controllers\API\ProgramController');
    Route::apiResource('circle', 'App\Http\Controllers\API\CircleController');
    Route::apiResource('event', 'App\Http\Controllers\API\EventController');
    Route::apiResource('password_reset', 'App\Http\Controllers\API\PasswordResetController')->except(['searchByEmailOrPhone', 'searchByEmail', 'searchByPhone', 'checkToken']);
    Route::apiResource('personal_access_token', 'App\Http\Controllers\API\PersonalAccessTokenController');
    Route::apiResource('message', 'App\Http\Controllers\API\MessageController');
    Route::apiResource('notification', 'App\Http\Controllers\API\NotificationController');
    Route::apiResource('read_notification', 'App\Http\Controllers\API\ReadNotificationController');
    Route::apiResource('toxic_content', 'App\Http\Controllers\API\ToxicContentController');
    Route::apiResource('payment', 'App\Http\Controllers\API\PaymentController')->except(['store', 'find_by_order_number', 'find_by_order_number_user', 'switch_status']);
    Route::apiResource('session', 'App\Http\Controllers\API\SessionController');
});
/*
|--------------------------------------------------------------------------
| Custom API resource
|--------------------------------------------------------------------------
 */
Route::group(['middleware' => ['api', 'localization']], function () {
    Route::resource('country', 'App\Http\Controllers\API\CountryController');
    Route::resource('currency', 'App\Http\Controllers\API\CurrencyController');
    Route::resource('currencies_rate', 'App\Http\Controllers\API\CurrenciesRateController');
    Route::resource('status', 'App\Http\Controllers\API\StatusController');
    Route::resource('type', 'App\Http\Controllers\API\TypeController');
    Route::resource('category', 'App\Http\Controllers\API\CategoryController');
    Route::resource('report_reason', 'App\Http\Controllers\API\ReportReasonController');
    Route::resource('subscription', 'App\Http\Controllers\API\SubscriptionController');
    Route::resource('role', 'App\Http\Controllers\API\RoleController');
    Route::resource('user', 'App\Http\Controllers\API\UserController');
    Route::resource('password_reset', 'App\Http\Controllers\API\PasswordResetController');
    Route::resource('payment', 'App\Http\Controllers\API\PaymentController');

    // Country
    Route::get('country', 'App\Http\Controllers\API\CountryController@index')->name('country.api.index');
    Route::post('country', 'App\Http\Controllers\API\CountryController@store')->name('country.api.store');
    Route::get('country/search/{data}', 'App\Http\Controllers\API\CountryController@search')->name('country.api.search');
    // Currency
    Route::get('currency', 'App\Http\Controllers\API\CurrencyController@index')->name('currency.api.index');
    Route::post('currency', 'App\Http\Controllers\API\CurrencyController@store')->name('currency.api.store');
    Route::get('currency/search/{data}', 'App\Http\Controllers\API\CurrencyController@search')->name('currency.api.search');
    // CurrenciesRate
    Route::post('currencies_rate', 'App\Http\Controllers\API\CurrenciesRateController@store')->name('currencies_rate.api.store');
    Route::get('currencies_rate/find_currency_rate/{from_currency_acronym}/{to_currency_acronym}', 'App\Http\Controllers\API\CurrenciesRateController@findCurrencyRate')->name('currencies_rate.api.find_currency_rate');
    // Status
    Route::get('status/search/{locale}/{data}', 'App\Http\Controllers\API\StatusController@search')->name('status.api.search');
    Route::get('status/find_by_group/{group_name}', 'App\Http\Controllers\API\StatusController@findByGroup')->name('status.api.find_by_group');
    // Type
    Route::get('type/search/{locale}/{data}', 'App\Http\Controllers\API\TypeController@search')->name('type.api.search');
    Route::get('type/find_by_group/{group_name}', 'App\Http\Controllers\API\TypeController@findByGroup')->name('type.api.find_by_group');
    // Category
    Route::get('category/search/{locale}/{data}', 'App\Http\Controllers\API\CategoryController@search')->name('category.api.search');
    Route::get('category/find_by_group/{group_name}', 'App\Http\Controllers\API\CategoryController@findByGroup')->name('category.api.find_by_group');
    Route::get('category/all_used_in_works', 'App\Http\Controllers\API\CategoryController@allUsedInWorks')->name('category.api.all_used_in_works');
    Route::get('category/all_used_in_works_type/{type_id}', 'App\Http\Controllers\API\CategoryController@allUsedInWorksType')->name('category.api.all_used_in_works_type');
    // ReportReason
    Route::post('report_reason', 'App\Http\Controllers\API\ReportReasonController@store')->name('report_reason.api.store');
    Route::get('report_reason/find_by_entity/{entity}', 'App\Http\Controllers\API\ReportReasonController@findByEntity')->name('report_reason.api.find_by_entity');
    // Subscription
    Route::get('subscription', 'App\Http\Controllers\API\SubscriptionController@index')->name('subscription.api.index');
    // Role
    Route::get('role/search/{data}', 'App\Http\Controllers\API\RoleController@search')->name('role.api.search');
    // User
    Route::post('user', 'App\Http\Controllers\API\UserController@store')->name('user.api.store');
    Route::get('user/{id}', 'App\Http\Controllers\API\UserController@show')->name('user.api.show');
    Route::get('user/profile/{username}', 'App\Http\Controllers\API\UserController@profile')->name('user.api.profile');
    Route::post('user/login', 'App\Http\Controllers\API\UserController@login')->name('user.api.login');
    // PasswordReset
    Route::get('password_reset/search_by_email_or_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmailOrPhone')->name('password_reset.api.search_by_email_or_phone');
    Route::get('password_reset/search_by_email/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByEmail')->name('password_reset.api.search_by_email');
    Route::get('password_reset/search_by_phone/{data}', 'App\Http\Controllers\API\PasswordResetController@searchByPhone')->name('password_reset.api.search_by_phone');
    Route::post('password_reset/check_token/{entity}', 'App\Http\Controllers\API\PasswordResetController@checkToken')->name('password_reset.api.check_token');
    // Payment
    Route::post('payment/store', 'App\Http\Controllers\API\PaymentController@store')->name('payment.api.store');
    Route::get('payment/find_by_phone/{phone_number}', 'App\Http\Controllers\API\PaymentController@findByPhone')->name('payment.api.find_by_phone');
    Route::get('payment/find_by_order_number/{order_number}', 'App\Http\Controllers\API\PaymentController@findByOrderNumber')->name('payment.api.find_by_order_number');
    Route::get('payment/find_by_order_number_user/{order_number}/{user_id}', 'App\Http\Controllers\API\PaymentController@findByOrderNumberUser')->name('payment.api.find_by_order_number_user');
    Route::put('payment/switch_status/{status_id}/{id}', 'App\Http\Controllers\API\PaymentController@switchStatus')->name('payment.api.switch_status');
});
Route::group(['middleware' => ['auth:sanctum', 'api', 'localization']], function () {
    Route::resource('work', 'App\Http\Controllers\API\WorkController');
    Route::resource('like', 'App\Http\Controllers\API\LikeController');
    Route::resource('partner', 'App\Http\Controllers\API\PartnerController');
    Route::resource('activation_code', 'App\Http\Controllers\API\ActivationCodeController');
    Route::resource('cart', 'App\Http\Controllers\API\CartController');
    Route::resource('subscription', 'App\Http\Controllers\API\SubscriptionController')->except(['index']);
    Route::resource('user', 'App\Http\Controllers\API\UserController')->except(['store', 'show', 'login']);
    Route::resource('circle', 'App\Http\Controllers\API\CircleController');
    Route::resource('organization', 'App\Http\Controllers\API\OrganizationController');
    Route::resource('program', 'App\Http\Controllers\API\ProgramController');
    Route::resource('event', 'App\Http\Controllers\API\EventController');
    Route::resource('message', 'App\Http\Controllers\API\MessageController');
    Route::resource('notification', 'App\Http\Controllers\API\NotificationController');
    Route::resource('read_notification', 'App\Http\Controllers\API\ReadNotificationController');
    Route::resource('toxic_content', 'App\Http\Controllers\API\ToxicContentController');

    // Work
    Route::get('work/trends/{year}', 'App\Http\Controllers\API\WorkController@trends')->name('work.api.trends');
    Route::get('work/find_all_by_entity/{entity}/{entity_id}', 'App\Http\Controllers\API\WorkController@findAllByEntity')->name('work.api.find_all_by_entity');
    Route::get('work/find_all_by_type/{locale}/{type_name}', 'App\Http\Controllers\API\WorkController@findAllByType')->name('work.api.find_all_by_type');
    Route::get('work/find_views/{work_id}', 'App\Http\Controllers\API\WorkController@findViews')->name('work.api.find_views');
    Route::get('work/find_likes/{work_id}', 'App\Http\Controllers\API\WorkController@findLikes')->name('work.api.find_likes');
    Route::put('work/switch_view/{work_id}', 'App\Http\Controllers\API\WorkController@switchView')->name('work.api.switch_view');
    Route::put('work/validate_consultations/{user_id}', 'App\Http\Controllers\API\WorkController@validateConsultations')->name('work.api.validate_consultations');
    Route::put('work/invalidate_consultations/{user_id}', 'App\Http\Controllers\API\WorkController@invalidateConsultations')->name('work.api.invalidate_consultations');
    Route::put('work/add_image/{id}', 'App\Http\Controllers\API\WorkController@addImage')->name('work.api.add_image');
    Route::post('work/search', 'App\Http\Controllers\API\WorkController@search')->name('work.api.search');
    Route::post('work/upload_files', 'App\Http\Controllers\API\WorkController@uploadFiles')->name('work.api.upload_files');
    Route::post('work/filter_by_categories', 'App\Http\Controllers\API\WorkController@filterByCategories')->name('work.api.filter_by_categories');
    // Like
    Route::delete('like/unlike_entity/{user_id}/{entity}/{entity_id}', 'App\Http\Controllers\API\LikeController@unlikeEntity')->name('like.api.unlike_entity');
    // Partner
    Route::get('partner/search/{data}', 'App\Http\Controllers\API\PartnerController@search')->name('partner.api.search');
    Route::get('partner/partnerships_by_status/{locale}/{status_name}', 'App\Http\Controllers\API\PartnerController@partnershipsByStatus')->name('partner.api.partnerships_by_status');
    Route::get('partner/partners_with_activation_code/{locale}/{status_name}', 'App\Http\Controllers\API\PartnerController@partnersWithActivationCode')->name('partner.api.partners_with_activation_code');
    Route::get('partner/users_with_promo_code/{partner_id}', 'App\Http\Controllers\API\PartnerController@usersWithPromoCode')->name('partner.api.users_with_promo_code');
    Route::put('partner/withdraw_some_categories/{partner_id}', 'App\Http\Controllers\API\PartnerController@withdrawSomeCategories')->name('partner.api.withdraw_some_categories');
    Route::put('partner/withdraw_all_categories/{partner_id}', 'App\Http\Controllers\API\PartnerController@withdrawSomeCategories')->name('partner.api.withdraw_all_categories');
    Route::put('partner/terminate_partnership/{partner_id}', 'App\Http\Controllers\API\PartnerController@terminatePartnership')->name('partner.api.terminate_partnership');
    // ActivationCode
    Route::get('activation_code/find_users_by_partner/{partner_id}', 'App\Http\Controllers\API\ActivationCodeController@findUsersByPartner')->name('activation_code.api.find_users_by_partner');
    Route::put('activation_code/activate_subscription/{user_id}/{code}/{partner_id}', 'App\Http\Controllers\API\ActivationCodeController@activateSubscription')->name('activation_code.api.activate_subscription');
    Route::put('activation_code/disable_subscription/{user_id}', 'App\Http\Controllers\API\ActivationCodeController@disableSubscription')->name('activation_code.api.disable_subscription');
    // Cart
    Route::put('cart/remove_from_cart/{cart_id}', 'App\Http\Controllers\API\CartController@removeFromCart')->name('cart.api.remove_from_cart');
    Route::post('cart/add_to_cart/{entity}', 'App\Http\Controllers\API\CartController@addToCart')->name('cart.api.add_to_cart');
    Route::post('cart/purchase/{user_id}', 'App\Http\Controllers\API\CartController@purchase')->name('cart.api.purchase');
    // Subscription
    Route::get('subscription/is_subscribed/{user_id}', 'App\Http\Controllers\API\SubscriptionController@isSubscribed')->name('subscription.api.is_subscribed');
    Route::put('subscription/validate_subscription/{user_id}', 'App\Http\Controllers\API\SubscriptionController@validateSubscription')->name('subscription.api.validate_subscription');
    Route::put('subscription/invalidate_subscription/{user_id}', 'App\Http\Controllers\API\SubscriptionController@invalidateSubscription')->name('subscription.api.invalidate_subscription');
    // User
    Route::get('user/find_by_role/{role_name}', 'App\Http\Controllers\API\UserController@findByRole')->name('user.api.find_by_role');
    Route::get('user/find_by_not_role/{role_name}', 'App\Http\Controllers\API\UserController@findByNotRole')->name('user.api.find_by_not_role');
    Route::get('user/works_subscribers/{user_id}', 'App\Http\Controllers\API\UserController@worksSubscribers')->name('user.api.works_subscribers');
    Route::get('user/organization_members/{organization_id}/{role_name}', 'App\Http\Controllers\API\UserController@organizationMembers')->name('user.api.organization_members');
    Route::get('user/group_members/{entity}/{entity_id}', 'App\Http\Controllers\API\UserController@groupMembers')->name('user.api.group_members');
    Route::get('user/member_groups/{entity}/{user_id}/{status_id}', 'App\Http\Controllers\API\UserController@memberGroups')->name('user.api.member_groups');
    Route::get('user/is_main_member/{entity}/{entity_id}/{user_id}', 'App\Http\Controllers\API\UserController@isMainMember')->name('user.api.is_main_member');
    Route::get('user/is_partner/{user_id}', 'App\Http\Controllers\API\UserController@isPartner')->name('user.api.is_partner');
    Route::get('user/find_by_status/{status_id}', 'App\Http\Controllers\API\UserController@findByStatus')->name('user.api.find_by_status');
    Route::put('user/switch_status/{id}/{status_id}', 'App\Http\Controllers\API\UserController@switchStatus')->name('user.api.switch_status');
    Route::put('user/update_role/{action}/{id}', 'App\Http\Controllers\API\UserController@updateRole')->name('user.api.update_role');
    Route::put('user/update_user_membership/{entity}/{entity_id}/{action}/{id}', 'App\Http\Controllers\API\UserController@updateUserMembership')->name('user.api.update_user_membership');
    Route::put('user/update_password/{id}', 'App\Http\Controllers\API\UserController@updatePassword')->name('user.api.update_password');
    Route::put('user/subscribe_to_group/{user_id}/{addressee_id}', 'App\Http\Controllers\API\UserController@subscribeToGroup')->name('user.api.subscribe_to_group');
    Route::put('user/unsubscribe_to_group/{user_id}/{addressee_id}', 'App\Http\Controllers\API\UserController@unsubscribeToGroup')->name('user.api.unsubscribe_to_group');
    Route::put('user/update_avatar_picture/{id}', 'App\Http\Controllers\API\UserController@updateAvatarPicture')->name('user.api.update_avatar_picture');
    Route::post('user/search', 'App\Http\Controllers\API\UserController@search')->name('user.api.search');
    // Organization
    Route::post('organization/search', 'App\Http\Controllers\API\OrganizationController@search')->name('organization.api.search');
    Route::get('organization/find_all_by_type/{type_id}', 'App\Http\Controllers\API\OrganizationController@findAllByType')->name('organization.api.find_all_by_type');
    Route::get('organization/find_all_by_owner/{user_id}', 'App\Http\Controllers\API\OrganizationController@findAllByOwner')->name('organization.api.find_all_by_owner');
    // Circle
    Route::post('circle/search', 'App\Http\Controllers\API\CircleController@search')->name('circle.api.search');
    // Program
    Route::get('program/find_all_by_year_and_organization/{course_year}/{organization_id}', 'App\Http\Controllers\API\ProgramController@findAllByYearAndOrganization')->name('program.api.find_all_by_year_and_organization');
    Route::post('program/add_organization_program/{organization_id}', 'App\Http\Controllers\API\ProgramController@addOrganizationProgram')->name('program.api.add_organization_program');
    // Event
    Route::get('event/find_by_type/{locale}/{type_name}', 'App\Http\Controllers\API\EventController@findByType')->name('event.api.find_by_type');
    Route::get('event/find_by_status/{locale}/{status_name}', 'App\Http\Controllers\API\EventController@findByStatus')->name('event.api.find_by_status');
    Route::get('event/find_by_organization/{organization_id}', 'App\Http\Controllers\API\EventController@findByOrganization')->name('event.api.find_by_organization');
    Route::get('event/find_speakers/{event_id}', 'App\Http\Controllers\API\EventController@findSpeakers')->name('event.api.find_speakers');
    Route::put('event/update_cover/{event_id}', 'App\Http\Controllers\API\EventController@update_cover')->name('event.api.update_cover');
    Route::post('event/search', 'App\Http\Controllers\API\EventController@search')->name('event.api.search');
    Route::post('event/filter_for_organization/{organization_id}', 'App\Http\Controllers\API\EventController@filterForOrganization')->name('event.api.filter_for_organization');
    Route::post('event/filter_for_everybody', 'App\Http\Controllers\API\EventController@filterForEverybody')->name('event.api.filter_for_everybody');
    // Message
    Route::get('message/search_in_chat/{locale}/{type_name}/{data}/{sender_id}/{addressee_id}', 'App\Http\Controllers\API\MessageController@searchInChat')->name('message.api.search_in_chat');
    Route::get('message/search_in_group/{entity}/{entity_id}/{member_id}/{data}', 'App\Http\Controllers\API\MessageController@searchInGroup')->name('message.api.search_in_group');
    Route::get('message/find_by_group/{entity}/{entity_id}', 'App\Http\Controllers\API\MessageController@findByGroup')->name('message.api.find_by_group');
    Route::get('message/user_chats_list/{locale}/{type_name}/{user_id}', 'App\Http\Controllers\API\MessageController@userChatsList')->name('message.api.user_chats_list');
    Route::get('message/selected_chat/{locale}/{type_name}/{user_id}/{entity}/{entity_id}', 'App\Http\Controllers\API\MessageController@selectedChat')->name('message.api.selected_chat');
    Route::get('message/members_with_message_status/{locale}/{status_name}/{message_id}', 'App\Http\Controllers\API\MessageController@membersWithMessageStatus')->name('message.api.members_with_message_status');
    Route::put('message/switch_like/{message_id}/{user_id}', 'App\Http\Controllers\API\MessageController@switchLike')->name('message.api.switch_like');
    Route::put('message/switch_report/{message_id}/{user_id}', 'App\Http\Controllers\API\MessageController@switchReport')->name('message.api.switch_report');
    Route::put('message/delete_for_myself/{user_id}/{message_id}', 'App\Http\Controllers\API\MessageController@deleteForMyself')->name('message.api.delete_for_myself');
    Route::put('message/delete_for_everybody/{message_id}', 'App\Http\Controllers\API\MessageController@deleteForEverybody')->name('message.api.delete_for_everybody');
    Route::put('message/mark_all_read_user/{locale}/{type_name}/{sender_id}/{addressee_user_id}', 'App\Http\Controllers\API\MessageController@markAllReadUser')->name('message.api.mark_all_read_user');
    Route::put('message/mark_all_read_group/{user_id}/{entity}/{entity_id}', 'App\Http\Controllers\API\MessageController@markAllReadGroup')->name('message.api.mark_all_read_group');
    // Notification
    Route::get('notification/select_by_user/{user_id}', 'App\Http\Controllers\API\NotificationController@selectByUser')->name('notification.api.select_by_user');
    Route::get('notification/select_by_status_user/{status_id}/{user_id}', 'App\Http\Controllers\API\NotificationController@selectByStatusUser')->name('notification.api.select_by_status_user');
    Route::put('notification/switch_status/{ids}/{status_id}', 'App\Http\Controllers\API\NotificationController@switchStatus')->name('notification.api.switch_status');
    Route::put('notification/mark_all_read/{user_id}', 'App\Http\Controllers\API\NotificationController@markAllRead')->name('notification.api.mark_all_read');
    // ReadNotification
    Route::get('read_notification/select_by_user/{user_id}', 'App\Http\Controllers\API\ReadNotificationController@selectByUser')->name('read_notification.api.select_by_user');
    // ToxicContent
    Route::put('toxic_content/unlock_user/{id}', 'App\Http\Controllers\API\ToxicContentController@unlockUser')->name('toxic_content.api.unlock_user');
});
