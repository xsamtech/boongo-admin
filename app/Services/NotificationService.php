<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\ReadNotification;
use App\Models\Product;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class NotificationService
{
    public function getUserNotifications($user_id): array
    {
        // === 1. Non lues (groupées)
        $unreadNotifications = Notification::where('to_user_id', $user_id)
            ->where('is_read', 0)
            ->orderByDesc('created_at')
            ->get();

        $grouped = $this->groupNotifications($unreadNotifications);

        // === 2. Déjà lues
        $readNotifications = ReadNotification::where('to_user_id', $user_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($notif) {
                return [
                    'text' => $notif->text_content,
                    'url' => $notif->redirect_url,
                    'created_at' => $notif->created_at,
                    'is_read' => true,
                ];
            });

        return [
            'unread' => $grouped,
            'read' => $readNotifications,
        ];
    }

    private function groupNotifications(Collection $notifications): array
    {
        return $notifications
            ->groupBy(function ($notif) {
                // Group by: type + entity ID
                return $notif->type . '-' . $this->getEntityId($notif);
            })
            ->map(function ($group) {
                return $this->formatNotificationGroup($group);
            })
            ->values()
            ->toArray();
    }

    private function getEntityId($notif)
    {
        return $notif->product_id
            ?? $notif->crowdfunding_id
            ?? $notif->project_id
            ?? $notif->post_id
            ?? null;
    }

    private function formatNotificationGroup(Collection $group): array
    {
        $first = $group->first();
        $type = $first->type;
        $count = $group->count();
        $is_many = $count > 1;

        $key = $is_many ? 'many' : 'one';
        $params = $this->buildParameters($group, $key);

        $text = Lang::get("notifications.$type.$key", $params);

        return [
            'text' => $text,
            'url' => $this->generateUrl($type, $first),
            'created_at' => $first->created_at,
            'is_read' => false,
            'notification_ids' => $group->pluck('id'),
        ];
    }

    private function buildParameters(Collection $group, string $key): array
    {
        $notif = $group->first();
        $params = [];

        // Paramètres dynamiques
        if (in_array($notif->type, ['stock_emptied', 'product_shared', 'product_blocked', 'customer_feedback'])) {
            $product = Product::find($notif->product_id);
            $params['product_name'] = $product->product_name ?? 'N/A';
            $params['product_type'] = $product->type ?? 'product';
        }

        if (in_array($notif->type, ['post_answered'])) {
            $post = Post::find($notif->post_id);
            $params['post_type'] = $post->type ?? 'post';
        }

        if (in_array($notif->type, ['product_published', 'project_published'])) {
            $user = User::find($notif->from_user_id);
            $params['user_name'] = $user->firstname . ' ' . $user->lastname ?? 'Someone';
            if ($notif->type === 'product_published') {
                $product = Product::find($notif->product_id);
                $params['product_type'] = __('miscellaneous.admin.product.entity.' . ($product->type ?? 'product') . '.singular');
            }
        }

        if ($key === 'many') {
            $params['count'] = $group->count();
        }

        return $params;
    }

    private function generateUrl($type, $notif): string|null
    {
        return match ($type) {
            'stock_emptied',
            'product_shared',
            'product_blocked',
            'product_published',
            'customer_feedback' => route('product.entity.datas', ['entity' => $notif->product->type, 'id' => $notif->product_id]),

            'project_published',
            'project_shared',
            'project_blocked' => route('crowdfunding.datas', ['id' => $notif->project_id]),

            'post_answered' => route('discussion.datas', ['id' => $notif->post_id]),

            'complaint_sent' => route('dashboard.complaints.home'),

            'news_sent' => route('news.index'),

            default => null
        };
    }
}
