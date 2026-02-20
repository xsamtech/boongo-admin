<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 1️⃣ Register broadcast routes
        // Laravel crée automatiquement la route POST /broadcasting/auth
        // Mais comme tu veux la placer sous /api, tu peux désactiver celle-ci
        // et utiliser celle que tu as dans routes/api.php
        Broadcast::routes([
            'middleware' => ['auth:api'],
            'prefix' => 'api', // 👈 IMPORTANT : place la route sous /api/broadcasting/auth
        ]);

        // 🚀 2️⃣ Include channels definitions
        require base_path('routes/channels.php');

        // 🚀 3️⃣ Debug logs (optionnel mais très utile)
        Broadcast::channel('*', function ($user) {
            Log::info('✅ Broadcast channel auth OK', [
                'user_id' => $user->id ?? null,
                'user_type' => get_class($user),
            ]);
            return true;
        });
    }
}
