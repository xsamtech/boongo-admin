<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\ToxicContent;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;

class ManagerController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        return $this->render('manager_dashboard', $request);
    }

    public function members(Request $request)
    {
        return $this->render('manager_members', $request);
    }

    public function membersDatas($id, Request $request)
    {
        return $this->render('manager_members', $request, ['selectedId' => (int) $id]);
    }

    public function establishments(Request $request)
    {
        return $this->render('manager_establishments', $request);
    }

    public function establishmentsDatas($id, Request $request)
    {
        return $this->render('manager_establishments', $request, ['selectedId' => (int) $id]);
    }

    public function institutions(Request $request)
    {
        return $this->render('manager_institutions', $request);
    }

    public function institutionsDatas($id, Request $request)
    {
        return $this->render('manager_institutions', $request, ['selectedId' => (int) $id]);
    }

    public function reported(Request $request)
    {
        return $this->render('manager_reported', $request);
    }

    public function notifications(Request $request)
    {
        return $this->render('manager_notifications', $request);
    }

    public function workDatas($id, Request $request)
    {
        return $this->render('manager_work', $request, ['selectedId' => (int) $id]);
    }

    public function reportedDatas($id, Request $request)
    {
        return $this->render('manager_reported', $request, ['selectedId' => (int) $id]);
    }

    public function addMembers(Request $request) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateMembers(Request $request, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addEstablishments(Request $request) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateEstablishments(Request $request, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addInstitutions(Request $request) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateInstitutions(Request $request, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addReported(Request $request) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateReported(Request $request, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }

    private function render(string $pageKey, Request $request, array $payload = [])
    {
        $titles = [
            'manager_dashboard' => ['Manager', 'Tableau de bord manager'],
            'manager_members' => ['Membres', 'Membres geres par le manager'],
            'manager_establishments' => ['Etablissements', 'Etablissements suivis'],
            'manager_institutions' => ['Institutions', 'Institutions suivies'],
            'manager_reported' => ['Signales', 'Elements signales'],
            'manager_notifications' => ['Notifications', 'Notifications du manager'],
            'manager_work' => ['Oeuvre', 'Details de l oeuvre'],
        ];

        [$cards, $table, $meta] = $this->buildData($pageKey, $payload);
        [$title, $description] = $titles[$pageKey] ?? ['Manager', 'Espace manager'];
        $form = null;

        return view('admin.section', compact('pageKey', 'title', 'description', 'payload', 'cards', 'table', 'meta', 'form'));
    }

    private function buildData(string $pageKey, array $payload = []): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Administrateur');
        $isManager = $user->hasRole('Manager');

        $cards = [];
        $table = ['columns' => [], 'rows' => []];
        $meta = [];

        if ($pageKey === 'manager_dashboard') {
            $membersQuery = $this->membersQuery($isAdmin, $isManager, (string) ($user->city ?? ''));
            $cards = [
                ['label' => 'Membres visibles', 'value' => $membersQuery->count()],
                ['label' => 'Etablissements', 'value' => Organization::count()],
                ['label' => 'Elements signales', 'value' => ToxicContent::count()],
            ];
        }

        if ($pageKey === 'manager_members') {
            $rows = $this->membersQuery($isAdmin, $isManager, (string) ($user->city ?? ''))
                ->with(['roles', 'status'])
                ->latest()
                ->limit(100)
                ->get()
                ->map(function ($u) {
                    $statusName = '-';
                    $raw = $u->status?->status_name;
                    if (is_array($raw)) {
                        $statusName = $raw['fr'] ?? ($raw['en'] ?? '-');
                    } elseif (is_string($raw)) {
                        $decoded = json_decode($raw, true);
                        $statusName = is_array($decoded) ? ($decoded['fr'] ?? ($decoded['en'] ?? $raw)) : $raw;
                    }
                    return [
                        'id' => (string) $u->id,
                        'nom' => trim(($u->firstname ?? '') . ' ' . ($u->lastname ?? '')) ?: ('User #' . $u->id),
                        'email' => $u->email ?? '-',
                        'ville' => $u->city ?? '-',
                        'roles' => $u->roles->pluck('role_name')->implode(', ') ?: '-',
                        'etat' => $statusName,
                        '_view_url' => route('manager.members.datas', ['id' => $u->id]),
                        '_delete_url' => null,
                    ];
                })->toArray();
            $table = ['columns' => ['id', 'nom', 'email', 'ville', 'roles', 'etat'], 'rows' => $rows];
        }

        if ($pageKey === 'manager_establishments' || $pageKey === 'manager_institutions') {
            $rows = Organization::latest()->limit(100)->get()->map(function ($o) use ($pageKey) {
                return [
                    'id' => (string) $o->id,
                    'nom' => $o->organization_name ?? $o->name ?? ('#' . $o->id),
                    'ville' => $o->city ?? '-',
                    'date' => optional($o->created_at)->format('Y-m-d H:i'),
                    '_view_url' => $pageKey === 'manager_establishments' ? route('manager.establishments.datas', ['id' => $o->id]) : route('manager.institutions.datas', ['id' => $o->id]),
                    '_delete_url' => null,
                ];
            })->toArray();
            $table = ['columns' => ['id', 'nom', 'ville', 'date'], 'rows' => $rows];
        }

        if ($pageKey === 'manager_reported') {
            $rows = ToxicContent::with(['user', 'report_reason'])->latest()->limit(100)->get()->map(function ($r) {
                $reasonContent = '-';
                $raw = $r->report_reason?->reason_content;
                if (is_array($raw)) {
                    $reasonContent = $raw['fr'] ?? ($raw['en'] ?? '-');
                } elseif (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    $reasonContent = is_array($decoded) ? ($decoded['fr'] ?? ($decoded['en'] ?? $raw)) : $raw;
                }
                return [
                    'id' => (string) $r->id,
                    'motif' => $reasonContent,
                    'signaleur' => trim(($r->user?->firstname ?? '') . ' ' . ($r->user?->lastname ?? '')) ?: ($r->user?->email ?? '-'),
                    'date' => optional($r->created_at)->format('Y-m-d H:i'),
                    '_view_url' => route('manager.reported.datas', ['id' => $r->id]),
                    '_delete_url' => null,
                ];
            })->toArray();
            $table = ['columns' => ['id', 'motif', 'signaleur', 'date'], 'rows' => $rows];
        }

        if ($pageKey === 'manager_notifications') {
            $notifications = Notification::with(['from_user', 'type', 'work', 'like.for_work'])
                ->where('to_user_id', auth()->id())
                ->whereHas('type', fn (Builder $q) => $q->whereRaw("RIGHT(alias, 6) = '_notif'"))
                ->latest()
                ->limit(220)
                ->get();

            $meta['notification_feed'] = $notifications->map(function ($n) {
                $fromName = trim(($n->from_user->firstname ?? '') . ' ' . ($n->from_user->lastname ?? '')) ?: ($n->from_user->username ?? 'Systeme');
                $alias = $n->type?->alias;
                return [
                    'id' => $n->id,
                    'from' => $fromName,
                    'text' => ($alias && Lang::has('notifications.' . $alias))
                        ? __('notifications.' . $alias, ['from_user_names' => $fromName])
                        : __('messages.notifications.default'),
                    'date' => optional($n->created_at)->diffForHumans(),
                    'created_at' => optional($n->created_at)->format('Y-m-d H:i'),
                    'icon' => $n->type?->icon ?: 'feather-bell',
                    'view_url' => !empty($n->work_id)
                        ? route('manager.work.datas', ['id' => $n->work_id])
                        : (($n->like && !empty($n->like->for_work_id))
                            ? route('manager.work.datas', ['id' => $n->like->for_work_id])
                            : ((!empty($n->event_id) || !empty($n->circle_id))
                                ? route('manager.members.datas', ['id' => (int) $n->from_user_id])
                                : route('manager.notifications.home', ['focus' => $n->id]))),
                ];
            })->values()->toArray();
        }

        if ($pageKey === 'manager_work') {
            $work = Work::with(['user_owner', 'type', 'status'])->find((int) ($payload['selectedId'] ?? 0));
            if ($work) {
                $typeName = '-';
                $typeRaw = $work->type?->type_name;
                if (is_array($typeRaw)) {
                    $typeName = $typeRaw['fr'] ?? ($typeRaw['en'] ?? '-');
                } elseif (is_string($typeRaw)) {
                    $decoded = json_decode($typeRaw, true);
                    $typeName = is_array($decoded) ? ($decoded['fr'] ?? ($decoded['en'] ?? $typeRaw)) : $typeRaw;
                }
                $statusName = '-';
                $statusRaw = $work->status?->status_name;
                if (is_array($statusRaw)) {
                    $statusName = $statusRaw['fr'] ?? ($statusRaw['en'] ?? '-');
                } elseif (is_string($statusRaw)) {
                    $decoded = json_decode($statusRaw, true);
                    $statusName = is_array($decoded) ? ($decoded['fr'] ?? ($decoded['en'] ?? $statusRaw)) : $statusRaw;
                }
                $meta['detail'] = [
                    'title' => 'Oeuvre #' . $work->id,
                    'items' => [
                        'ID' => $work->id,
                        'Titre' => $work->work_title ?? '-',
                        'Auteur' => trim(($work->user_owner->firstname ?? '') . ' ' . ($work->user_owner->lastname ?? '')) ?: ($work->user_owner->email ?? '-'),
                        'Type' => $typeName,
                        'Etat' => $statusName,
                    ],
                ];
            }
        }

        return [$cards, $table, $meta];
    }

    private function membersQuery(bool $isAdmin, bool $isManager, string $city): Builder
    {
        $query = User::where('id', '<>', auth()->id())
            ->whereHas('roles', function (Builder $q) {
                $q->whereRaw('LOWER(role_name) LIKE ?', ['%membre%'])
                    ->orWhereRaw('LOWER(role_name) LIKE ?', ['%publieur%'])
                    ->orWhereRaw('LOWER(role_name) LIKE ?', ['%publisher%']);
            });

        if ($isManager && !$isAdmin) {
            $query->where('city', $city);
        }

        return $query;
    }
}
