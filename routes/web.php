<?php

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ManagerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| ROUTES FOR EVERY ROLES
|--------------------------------------------------------------------------
*/
// Generate symbolic link
Route::get('/symlink', function () {
    return view('symlink');
})->name('generate_symlink');
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/language/{locale}', [HomeController::class, 'changeLanguage'])->name('change_language');
Route::post('/download', [HomeController::class, 'download'])->name('download');
Route::get('/notifications', [HomeController::class, 'notification'])->name('notification.home');
Route::get('/about', [HomeController::class, 'about'])->name('about.home');
Route::get('/about/{entity}', [HomeController::class, 'aboutEntity'])->name('about.entity');
Route::get('/books', [HomeController::class, 'book'])->name('book.home');
Route::get('/books/{id}', [HomeController::class, 'bookDatas'])->whereNumber('id')->name('book.datas');
Route::get('/newspapers', [HomeController::class, 'newspaper'])->name('newspaper.home');
Route::get('/newspapers/{id}', [HomeController::class, 'newspaperDatas'])->whereNumber('id')->name('newspaper.datas');
Route::get('/maps', [HomeController::class, 'map'])->name('map.home');
Route::get('/maps/{id}', [HomeController::class, 'mapDatas'])->whereNumber('id')->name('map.datas');
Route::get('/medias', [HomeController::class, 'media'])->name('media.home');
Route::get('/medias/{id}', [HomeController::class, 'mediaDatas'])->whereNumber('id')->name('media.datas');
// Account
Route::get('/account', [AccountController::class, 'account'])->name('account');
Route::post('/account', [AccountController::class, 'updateAccount']);
Route::get('/account/{entity}', [AccountController::class, 'accountEntity'])->name('account.entity');
Route::post('/account/{entity}', [AccountController::class, 'updateAccountEntity']);
Route::get('/account/{entity}/{id}', [AccountController::class, 'accountEntityDatas'])->whereNumber('id')->name('account.entity.datas');
Route::post('/account/{entity}/{id}', [AccountController::class, 'updateAccountEntityDatas']);
// Subscription
Route::get('/subscribe', [HomeController::class, 'subscribe'])->name('subscribe');
Route::post('/subscribe', [HomeController::class, 'runSubscribe']);
Route::get('/transaction_waiting', [HomeController::class, 'transactionWaiting'])->name('transaction.waiting');
Route::get('/transaction_message/{orderNumber}/{userId}', [HomeController::class, 'transactionMessage'])->name('transaction.message');
Route::get('/subscribed/{amount}/{currency}/{code}/{user_id}', [HomeController::class, 'subscribed'])->whereNumber(['amount', 'code'])->name('subscribed');
// Delete something
Route::delete('/delete/{entity}/{id}', [HomeController::class, 'removeData'])->whereNumber('id')->name('data.delete');

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Admin"
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Administrateur'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.home');
    Route::get('/admin/country', [AdminController::class, 'country'])->name('admin.country.home');
    Route::post('/admin/country', [AdminController::class, 'addCountry']);
    Route::get('/admin/country/{id}', [AdminController::class, 'countryDatas'])->whereNumber('id')->name('admin.country.datas');
    Route::post('/admin/country/{id}', [AdminController::class, 'updateCountry'])->whereNumber('id');
    Route::get('/admin/currency', [AdminController::class, 'currency'])->name('admin.currency.home');
    Route::post('/admin/currency', [AdminController::class, 'addCurrency']);
    Route::get('/admin/currency/{id}', [AdminController::class, 'currencyDatas'])->whereNumber('id')->name('admin.currency.datas');
    Route::post('/admin/currency/{id}', [AdminController::class, 'updateCurrency'])->whereNumber('id');
    Route::get('/admin/currency/{entity}', [AdminController::class, 'currencyEntity'])->name('admin.currency.entity.home');
    Route::post('/admin/currency/{entity}', [AdminController::class, 'addCurrencyEntity']);
    Route::get('/admin/currency/{entity}/{id}', [AdminController::class, 'currencyEntityDatas'])->whereNumber('id')->name('admin.currency.entity.datas');
    Route::post('/admin/currency/{entity}/{id}', [AdminController::class, 'updateCurrencyEntity'])->whereNumber('id');
    Route::get('/admin/role', [AdminController::class, 'role'])->name('admin.role.home');
    Route::post('/admin/role', [AdminController::class, 'addRole']);
    Route::get('/admin/role/{id}', [AdminController::class, 'roleDatas'])->whereNumber('id')->name('admin.role.datas');
    Route::post('/admin/role/{id}', [AdminController::class, 'updateRole'])->whereNumber('id');
    Route::get('/admin/role/{entity}', [AdminController::class, 'roleEntity'])->name('admin.role.entity.home');
    Route::post('/admin/role/{entity}', [AdminController::class, 'addRoleEntity']);
    Route::get('/admin/role/{entity}/{id}', [AdminController::class, 'roleEntityDatas'])->whereNumber('id')->name('admin.role.entity.datas');
    Route::post('/admin/role/{entity}/{id}', [AdminController::class, 'updateRoleEntity'])->whereNumber('id');
    Route::get('/admin/group', [AdminController::class, 'group'])->name('admin.group.home');
    Route::post('/admin/group', [AdminController::class, 'addGroup']);
    Route::get('/admin/group/{id}', [AdminController::class, 'groupDatas'])->whereNumber('id')->name('admin.group.datas');
    Route::post('/admin/group/{id}', [AdminController::class, 'updateGroup'])->whereNumber('id');
    Route::get('/admin/group/{entity}', [AdminController::class, 'groupEntity'])->name('admin.group.entity.home');
    Route::post('/admin/group/{entity}', [AdminController::class, 'addGroupEntity']);
    Route::get('/admin/group/{entity}/{id}', [AdminController::class, 'groupEntityDatas'])->whereNumber('id')->name('admin.group.entity.datas');
    Route::post('/admin/group/{entity}/{id}', [AdminController::class, 'updateGroupEntity'])->whereNumber('id');
    Route::get('/admin/report-reason', [AdminController::class, 'reportReason'])->name('admin.report_reason.home');
    Route::post('/admin/report-reason', [AdminController::class, 'addReportReason']);
    Route::get('/admin/report-reason/{id}', [AdminController::class, 'reportReasonDatas'])->whereNumber('id')->name('admin.report_reason.datas');
    Route::post('/admin/report-reason/{id}', [AdminController::class, 'updateReportReason'])->whereNumber('id');
    Route::get('/admin/report-reason/{entity}', [AdminController::class, 'reportReasonEntity'])->name('admin.report_reason.entity.home');
    Route::post('/admin/report-reason/{entity}', [AdminController::class, 'addReportReasonEntity']);
    Route::get('/admin/report-reason/{entity}/{id}', [AdminController::class, 'reportReasonEntityDatas'])->whereNumber('id')->name('admin.report_reason.entity.datas');
    Route::post('/admin/report-reason/{entity}/{id}', [AdminController::class, 'updateReportReasonEntity'])->whereNumber('id');
    Route::get('/admin/subscription', [AdminController::class, 'subscription'])->name('admin.subscription.home');
    Route::post('/admin/subscription', [AdminController::class, 'addSubscription']);
    Route::get('/admin/subscription/{id}', [AdminController::class, 'subscriptionDatas'])->whereNumber('id')->name('admin.subscription.datas');
    Route::post('/admin/subscription/{id}', [AdminController::class, 'updateSubscription'])->whereNumber('id');
    Route::get('/admin/work', [AdminController::class, 'work'])->name('admin.work.home');
    Route::post('/admin/work', [AdminController::class, 'addWork']);
    Route::get('/admin/work/{id}', [AdminController::class, 'workDatas'])->whereNumber('id')->name('admin.work.datas');
    Route::post('/admin/work/{id}', [AdminController::class, 'updateWork'])->whereNumber('id');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.home');
    Route::get('/admin/users/filter', [AdminController::class, 'usersByRoleAjax'])->name('admin.users.filter');
    Route::get('/admin/users/{entity}/{id}/{section}', [AdminController::class, 'usersEntitySection'])->whereNumber('id')->name('admin.users.entity.section.home');
    Route::get('/admin/users/{entity}/{id}', [AdminController::class, 'usersEntityDatas'])->whereNumber('id')->name('admin.users.entity.datas');
    Route::get('/admin/users/{entity}', [AdminController::class, 'usersEntity'])->name('admin.users.entity.home');
    Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications.home');
    Route::get('/admin/search/suggest', [AdminController::class, 'searchSuggestions'])->name('admin.search.suggest');
    Route::delete('/admin/delete/{entity}/{id}', [AdminController::class, 'deleteEntity'])->whereNumber('id')->name('admin.data.delete');
    Route::patch('/admin/users/{id}/role', [AdminController::class, 'updateUserRole'])->whereNumber('id')->name('admin.users.update_role');
    Route::patch('/admin/users/{id}/status', [AdminController::class, 'updateUserStatus'])->whereNumber('id')->name('admin.users.update_status');
    Route::patch('/admin/work/{id}/status', [AdminController::class, 'updateWorkStatus'])->whereNumber('id')->name('admin.work.update_status');
});

Route::middleware(['auth', 'role:Administrateur,Manager'])->group(function () {
    Route::get('/search/suggest', [AdminController::class, 'searchSuggestions'])->name('app.search.suggest');
});

/*
|--------------------------------------------------------------------------
| ROUTES FOR "Manager"
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/manager', [ManagerController::class, 'dashboard'])->name('manager.home');
    Route::get('/manager/members', [ManagerController::class, 'members'])->name('manager.members.home');
    Route::post('/manager/members', [ManagerController::class, 'addMembers']);
    Route::get('/manager/members/{id}', [ManagerController::class, 'membersDatas'])->whereNumber('id')->name('manager.members.datas');
    Route::post('/manager/members/{id}', [ManagerController::class, 'updateMembers'])->whereNumber('id');
    Route::get('/manager/establishments', [ManagerController::class, 'establishments'])->name('manager.establishments.home');
    Route::post('/manager/establishments', [ManagerController::class, 'addEstablishments']);
    Route::get('/manager/establishments/{id}', [ManagerController::class, 'establishmentsDatas'])->whereNumber('id')->name('manager.establishments.datas');
    Route::post('/manager/establishments/{id}', [ManagerController::class, 'updateEstablishments'])->whereNumber('id');
    Route::get('/manager/institutions', [ManagerController::class, 'institutions'])->name('manager.institutions.home');
    Route::post('/manager/institutions', [ManagerController::class, 'addInstitutions']);
    Route::get('/manager/institutions/{id}', [ManagerController::class, 'institutionsDatas'])->whereNumber('id')->name('manager.institutions.datas');
    Route::post('/manager/institutions/{id}', [ManagerController::class, 'updateInstitutions'])->whereNumber('id');
    Route::get('/manager/reported', [ManagerController::class, 'reported'])->name('manager.reported.home');
    Route::post('/manager/reported', [ManagerController::class, 'addReported']);
    Route::get('/manager/reported/{id}', [ManagerController::class, 'reportedDatas'])->whereNumber('id')->name('manager.reported.datas');
    Route::post('/manager/reported/{id}', [ManagerController::class, 'updateReported'])->whereNumber('id');
    Route::get('/manager/notifications', [ManagerController::class, 'notifications'])->name('manager.notifications.home');
    Route::get('/manager/work/{id}', [ManagerController::class, 'workDatas'])->whereNumber('id')->name('manager.work.datas');
});

require __DIR__ . '/auth.php';
