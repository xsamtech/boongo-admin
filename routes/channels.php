<?php
/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
*/

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    Log::debug('🔍 Comparaison auth', [
        'auth_user_id' => $user->id,
        'channel_userId' => $userId,
        'egalite' => (int) $user->id === (int) $userId,
        'types' => [
            'auth_user_id' => gettype($user->id),
            'channel_userId' => gettype($userId),
        ]
    ]);

    return (int) $user->id === (int) $userId ? $user : false;
    // return (int) $user->id === (int) $userId;
    // return true;
});

Broadcast::channel('circle.{circleId}', function ($user, $circleId) {
    return $user->circles->pluck('id')->contains($circleId);
});

Broadcast::channel('organization.{orgId}', function ($user, $orgId) {
    return $user->organizations->pluck('id')->contains($orgId);
});

Broadcast::channel('event.{eventId}', function ($user, $eventId) {
    return $user->events->pluck('id')->contains($eventId);
});