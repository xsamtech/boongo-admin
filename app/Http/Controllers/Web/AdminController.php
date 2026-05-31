<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiClientManager;
use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\Category;
use App\Models\Country;
use App\Models\CurrenciesRate;
use App\Models\Currency;
use App\Models\File;
use App\Models\Group;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\ReportReason;
use App\Models\Role;
use App\Models\Status;
use App\Models\Subscription;
use App\Models\ToxicContent;
use App\Models\Type;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public static $api_client_manager;

    public function __construct()
    {
        $this::$api_client_manager = new ApiClientManager();
        $this->middleware('auth');
    }

    public function dashboard(Request $request) { return $this->renderAdminPage('dashboard', [], $request); }
    public function country(Request $request) { return $this->renderAdminPage('country', [], $request); }
    public function countryDatas($id, Request $request) { return $this->renderAdminPage('country', ['selectedId' => (int) $id], $request); }
    public function currency(Request $request) { return $this->renderAdminPage('currency', [], $request); }
    public function currencyDatas($id, Request $request) { return $this->renderAdminPage('currency', ['selectedId' => (int) $id], $request); }
    public function currencyEntity($entity, Request $request) { return $this->renderAdminPage($entity === 'rate' ? 'currency_rate' : 'currency_entity', ['entity' => $entity], $request); }
    public function currencyEntityDatas($entity, $id, Request $request) { return $this->renderAdminPage($entity === 'rate' ? 'currency_rate' : 'currency_entity', ['entity' => $entity, 'selectedId' => (int) $id], $request); }
    public function role(Request $request) { return $this->renderAdminPage('role', [], $request); }
    public function roleDatas($id, Request $request) { return $this->renderAdminPage('role', ['selectedId' => (int) $id], $request); }
    public function roleEntity($entity, Request $request) { return $this->renderAdminPage('role_entity', ['entity' => $entity], $request); }
    public function roleEntityDatas($entity, $id, Request $request) { return $this->renderAdminPage('role_entity', ['entity' => $entity, 'selectedId' => (int) $id], $request); }
    public function group(Request $request) { return $this->renderAdminPage('group', [], $request); }
    public function groupDatas($id, Request $request) { return $this->renderAdminPage('group', ['selectedId' => (int) $id], $request); }
    public function groupEntity($entity, Request $request) { return $this->renderAdminPage(match ($entity) {'category' => 'group_category', 'type' => 'group_type', 'state' => 'group_state', default => 'group_entity'}, ['entity' => $entity], $request); }
    public function groupEntityDatas($entity, $id, Request $request) { return $this->renderAdminPage(match ($entity) {'category' => 'group_category', 'type' => 'group_type', 'state' => 'group_state', default => 'group_entity'}, ['entity' => $entity, 'selectedId' => (int) $id], $request); }
    public function reportReason(Request $request) { return $this->renderAdminPage('report_reason', [], $request); }
    public function reportReasonDatas($id, Request $request) { return $this->renderAdminPage('report_reason', ['selectedId' => (int) $id], $request); }
    public function reportReasonEntity($entity, Request $request) { return $this->renderAdminPage($entity === 'reported' ? 'report_reason_reported' : 'report_reason_entity', ['entity' => $entity], $request); }
    public function reportReasonEntityDatas($entity, $id, Request $request) { return $this->renderAdminPage($entity === 'reported' ? 'report_reason_reported' : 'report_reason_entity', ['entity' => $entity, 'selectedId' => (int) $id], $request); }
    public function subscription(Request $request) { return $this->renderAdminPage('subscription', [], $request); }
    public function subscriptionDatas($id, Request $request) { return $this->renderAdminPage('subscription', ['selectedId' => (int) $id], $request); }
    public function organizations(Request $request) { return $this->renderAdminPage('organization', [], $request); }
    public function organizationsDatas($id, Request $request) { return $this->renderAdminPage('organization', ['selectedId' => (int) $id], $request); }
    public function work(Request $request) { return $this->renderAdminPage('work', [], $request); }
    public function workDatas($id, Request $request) { return $this->renderAdminPage('work', ['selectedId' => (int) $id], $request); }
    public function encoderDashboard(Request $request) { return $this->renderAdminPage('encoder_dashboard', [], $request); }
    public function encoderWork(Request $request) { return $this->renderAdminPage('encoder_work', [], $request); }
    public function encoderWorkDatas($id, Request $request) { return $this->renderAdminPage('encoder_work', ['selectedId' => (int) $id], $request); }
    public function users(Request $request) { return $this->renderAdminPage('users', [], $request); }
    public function usersEntity($entity, Request $request) { return $this->renderAdminPage(['admin' => 'users_admin', 'manager' => 'users_manager', 'member' => 'users_member', 'partner' => 'users_partner', 'sponsor' => 'users_sponsor', 'publisher' => 'users_publisher'][$entity] ?? 'users_entity', ['entity' => $entity], $request); }
    public function usersEntityDatas($entity, $id, Request $request) { return $this->renderAdminPage(['admin' => 'users_admin', 'manager' => 'users_manager', 'member' => 'users_member', 'partner' => 'users_partner', 'sponsor' => 'users_sponsor', 'publisher' => 'users_publisher'][$entity] ?? 'users_entity', ['entity' => $entity, 'selectedId' => (int) $id], $request); }
    public function usersEntitySection($entity, $id, $section, Request $request) { return $this->renderAdminPage(($entity === 'partner' && $section === 'activation-codes') ? 'users_partner_activation_codes' : (($entity === 'partner' && $section === 'members') ? 'users_partner_members' : (($entity === 'publisher' && $section === 'works') ? 'users_publisher_works' : (($entity === 'publisher' && $section === 'members') ? 'users_publisher_members' : 'users_entity_section'))), ['entity' => $entity, 'selectedId' => (int) $id, 'section' => $section], $request); }
    public function notifications(Request $request) { return $this->renderAdminPage('notifications', [], $request); }
    public function messages(Request $request) { return $this->renderAdminPage('messages', [], $request); }
    public function usersByRoleAjax(Request $request)
    {
        $role = trim((string) $request->get('role', ''));
        $users = $role === ''
            ? User::with(['roles', 'country', 'status'])->where('id', '<>', auth()->id())->latest()->limit(20)->get()
            : $this->usersByRole([$role])->where('id', '<>', auth()->id())->latest()->limit(20)->get();
        $table = $this->userRows($users, app()->getLocale());
        $table['rows'] = $this->attachRowActions('users', $table['rows']);
        $table = $this->applyDisplayIndex($table);
        return response()->json($table);
    }

    public function searchSuggestions(Request $request)
    {
        $term = trim((string) $request->get('term', ''));
        if (mb_strlen($term) < 1) return response()->json(['groups' => []]);
        $like = '%' . $term . '%';

        $groups = [
            ['key' => 'countries', 'label' => __('messages.autocomplete.countries'), 'items' => Country::where('country_name', 'like', $like)->limit(5)->get()->map(fn($c) => ['id' => $c->id, 'label' => $c->country_name, 'url' => route('admin.country.datas', ['id' => $c->id])])->values()],
            ['key' => 'types', 'label' => __('messages.autocomplete.types'), 'items' => Type::where('type_name->' . app()->getLocale(), 'like', $like)->limit(5)->get()->map(fn($t) => ['id' => $t->id, 'label' => $this->t($t, 'type_name', app()->getLocale()), 'url' => route('admin.group.entity.datas', ['entity' => 'type', 'id' => $t->id])])->values()],
            ['key' => 'statuses', 'label' => __('messages.autocomplete.statuses'), 'items' => Status::where('status_name->' . app()->getLocale(), 'like', $like)->limit(5)->get()->map(fn($s) => ['id' => $s->id, 'label' => $this->t($s, 'status_name', app()->getLocale()), 'url' => route('admin.group.entity.datas', ['entity' => 'state', 'id' => $s->id])])->values()],
            ['key' => 'works', 'label' => __('messages.autocomplete.works'), 'items' => Work::where('work_title', 'like', $like)->limit(5)->get()->map(fn($w) => ['id' => $w->id, 'label' => $w->work_title, 'url' => route('admin.work.datas', ['id' => $w->id])])->values()],
        ];

        return response()->json(['groups' => array_values(array_filter($groups, fn($g) => count($g['items']) > 0))]);
    }

    public function lookup(Request $request, string $entity)
    {
        $term = trim((string) $request->get('term', ''));
        if (mb_strlen($term) < 1) return response()->json(['items' => []]);
        $like = '%' . $term . '%';

        if ($entity === 'users') {
            $items = User::query()
                ->where(function (Builder $q) use ($like) {
                    $q->where('firstname', 'like', $like)
                        ->orWhere('lastname', 'like', $like)
                        ->orWhere('surname', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                })
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn ($user) => ['id' => (int) $user->id, 'label' => $this->userLabel($user) . ' - ' . ($user->email ?? ('#' . $user->id))])
                ->values();

            return response()->json(['items' => $items]);
        }

        if ($entity === 'organizations') {
            $items = Organization::query()
                ->where(function (Builder $q) use ($like) {
                    $q->where('org_name', 'like', $like)
                        ->orWhere('org_acronym', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                })
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($organization) {
                    $name = trim((string) ($organization->org_name ?? '')) ?: ('Organisation #' . $organization->id);
                    $email = trim((string) ($organization->email ?? ''));
                    return ['id' => (int) $organization->id, 'label' => $email !== '' ? ($name . ' - ' . $email) : $name];
                })
                ->values();

            return response()->json(['items' => $items]);
        }

        return response()->json(['items' => []], 404);
    }

    private function renderAdminPage(string $pageKey, array $payload = [], ?Request $request = null)
    {
        $titles = [
            'dashboard' => [__('messages.nav.dashboard'), __('messages.pages.dashboard')],
            'country' => [__('messages.nav.country'), __('messages.pages.country')],
            'currency' => [__('messages.nav.currency'), __('messages.pages.currency')],
            'currency_rate' => [__('messages.nav.currency_rate'), __('messages.pages.currency_rate')],
            'currency_entity' => [__('messages.nav.currency'), __('messages.pages.currency')],
            'role' => [__('messages.nav.role'), __('messages.pages.role')],
            'role_entity' => [__('messages.nav.role'), __('messages.pages.role')],
            'group' => [__('messages.nav.group'), __('messages.pages.group')],
            'group_category' => [__('messages.nav.category'), __('messages.pages.group_category')],
            'group_type' => [__('messages.nav.type'), __('messages.pages.group_type')],
            'group_state' => [__('messages.nav.state'), __('messages.pages.group_state')],
            'report_reason' => [__('messages.nav.report_reason'), __('messages.pages.report_reason')],
            'report_reason_reported' => [__('messages.nav.reported_elements'), __('messages.pages.report_reason_reported')],
            'report_reason_entity' => [__('messages.nav.report_reason'), __('messages.pages.report_reason')],
            'subscription' => [__('messages.nav.subscription'), __('messages.pages.subscription')],
            'organization' => [__('messages.nav.organization'), __('messages.pages.organization')],
            'work' => [__('messages.nav.work'), __('messages.pages.work')],
            'encoder_dashboard' => [__('messages.nav.encoder_dashboard'), __('messages.pages.encoder_dashboard')],
            'encoder_work' => [__('messages.nav.encoder_work'), __('messages.pages.encoder_work')],
            'users' => [__('messages.nav.users'), __('messages.pages.users')],
            'users_admin' => [__('messages.nav.users'), __('messages.pages.users')],
            'users_manager' => [__('messages.nav.users'), __('messages.pages.users')],
            'users_member' => [__('messages.nav.members'), __('messages.pages.users_member')],
            'users_partner' => [__('messages.nav.partner'), __('messages.pages.users_partner')],
            'users_partner_activation_codes' => [__('messages.pages.users_partner_activation_codes_title'), __('messages.pages.users_partner_activation_codes')],
            'users_partner_members' => [__('messages.pages.users_partner_members_title'), __('messages.pages.users_partner_members')],
            'users_sponsor' => [__('messages.nav.sponsor'), __('messages.pages.users_sponsor')],
            'users_publisher' => [__('messages.nav.users'), __('messages.pages.users_publisher')],
            'users_publisher_works' => [__('messages.pages.users_publisher_works_title'), __('messages.pages.users_publisher_works')],
            'users_publisher_members' => [__('messages.pages.users_publisher_members_title'), __('messages.pages.users_publisher_members')],
            'notifications' => [__('messages.nav.notifications'), __('messages.pages.notifications')],
            'messages' => [__('messages.nav.notifications'), __('messages.pages.messages')],
        ];
        $q = trim((string) (($request?->get('q')) ?? ''));
        [$cards, $table, $meta] = $this->buildPageData($pageKey, $payload, $q, $request);
        if (!empty($payload['selectedId'])) {
            $meta['back_url'] = $this->backUrlForPage($pageKey, $payload, $request);
        }
        [$title, $description] = $titles[$pageKey] ?? [__('messages.pages.default_title'), __('messages.pages.default_desc')];

        $form = $this->formForPage($pageKey);
        if (!empty($payload['selectedId']) && !empty($form)) {
            $meta['edit_target'] = $this->updateUrlForPage($pageKey, (int) $payload['selectedId'], $payload);
            $model = $this->resolveModelForPage($pageKey, (int) $payload['selectedId'], $payload);
            if (!empty($model) && !empty($form['fields'])) {
                $meta['detail_form_values'] = collect($form['fields'])->mapWithKeys(function ($field) use ($model) {
                    $name = $field['name'] ?? null;
                    if (empty($name)) return [];
                    $value = $model->{$name} ?? '';
                    if (is_array($value)) $value = $value['fr'] ?? ($value['en'] ?? '');
                    return [$name => $value];
                })->toArray();
            }
            if (in_array($pageKey, ['work', 'encoder_work'], true) && $model instanceof Work) {
                $seconds = is_null($model->media_length) ? null : (int) $model->media_length;
                $meta['detail_form_values'] = [
                    'work_title' => $model->work_title ?? '',
                    'work_content' => $model->work_content ?? '',
                    'work_url' => $model->work_url ?? '',
                    'media_length' => is_null($seconds) ? '' : sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60),
                    'author' => $model->author ?? '',
                    'editor' => $model->editor ?? '',
                    'is_public' => (string) ($model->is_public ?? 1),
                    'consultation_price' => $model->consultation_price ?? '',
                    'currency_id' => $model->currency_id ?? '',
                    'type_id' => $model->type_id ?? '',
                    'status_id' => $model->status_id ?? '',
                    'user_id' => $model->user_id ?? '',
                    'organization_id' => $model->organization_id ?? '',
                    'categories_ids' => $model->categories->pluck('id')->map(fn ($x) => (string) $x)->values()->toArray(),
                ];
            }
        }
        return view('admin.section', compact('pageKey', 'title', 'description', 'payload', 'cards', 'table', 'meta', 'form') + ['searchQuery' => $q]);
    }

    private function buildPageData(string $key, array $payload, string $q, ?Request $request = null): array
    {
        $cards = []; $table = ['columns' => [], 'rows' => []]; $meta = []; $locale = app()->getLocale();
        if ($key === 'dashboard') {
            $cards = [['label' => 'Oeuvres', 'value' => Work::count()], ['label' => 'Paiements', 'value' => Payment::count()], ['label' => 'Organisations', 'value' => Organization::count()], ['label' => 'Membres', 'value' => User::count()], ['label' => 'Partenaires', 'value' => $this->usersByRole(['partner'])->count()], ['label' => 'Sponsors', 'value' => $this->usersByRole(['sponsor'])->count()], ['label' => 'Publieurs', 'value' => $this->usersByRole(['publisher', 'publieur'])->count()], ['label' => 'Abonnements', 'value' => Subscription::count()]];
            $recentWorks = Work::with(['user_owner', 'type'])->latest()->limit(5)->get()->map(fn($w) => ['id' => (string) $w->id, 'title' => $w->work_title ?? '-', 'owner' => $this->userLabel($w->user_owner), 'type' => $w->type ? $this->t($w->type, 'type_name', $locale) : '-', 'date' => optional($w->created_at)->format('Y-m-d H:i')])->toArray();
            $recentOrganizations = Organization::latest()->limit(5)->get()->map(fn($o) => ['id' => (string) $o->id, 'name' => $o->organization_name ?? $o->name ?? ('#' . $o->id), 'city' => $o->city ?? '-', 'date' => optional($o->created_at)->format('Y-m-d H:i')])->toArray();
            $recentPartners = $this->usersByRole(['partner'])->latest()->limit(5)->get()->map(fn($u) => ['id' => (string) $u->id, 'name' => $this->userLabel($u), 'email' => $u->email ?? '-', 'city' => $u->city ?? '-', 'date' => optional($u->created_at)->format('Y-m-d H:i')])->toArray();
            $meta['dashboard_tables'] = [
                ['title' => __('messages.dashboard.recent_works'), 'columns' => ['id', 'title', 'owner', 'type', 'date'], 'rows' => $recentWorks],
                ['title' => __('messages.dashboard.recent_organizations'), 'columns' => ['id', 'name', 'city', 'date'], 'rows' => $recentOrganizations],
                ['title' => __('messages.dashboard.recent_partners'), 'columns' => ['id', 'name', 'email', 'city', 'date'], 'rows' => $recentPartners],
            ];
            $meta['dashboard_chart'] = [
                'labels' => ['Oeuvres', 'Organisations', 'Partenaires', 'Sponsors', 'Publieurs', 'Utilisateurs'],
                'series' => [Work::count(), Organization::count(), $this->usersByRole(['partner'])->count(), $this->usersByRole(['sponsor'])->count(), $this->usersByRole(['publisher', 'publieur'])->count(), User::count()],
            ];
        } elseif ($key === 'encoder_dashboard') {
            $workStatusGroupId = $this->statusGroupIdForPage('work');
            $relevantStatusId = $this->findStatusIdByNameNeedles(['pertinente', 'pertinent'], $workStatusGroupId);
            $declassifiedStatusId = $this->findStatusIdByNameNeedles(['declassee', 'declass'], $workStatusGroupId);
            $cards = [
                ['label' => __('messages.encoder.relevant_works'), 'value' => $relevantStatusId ? Work::where('status_id', $relevantStatusId)->count() : Work::count()],
                ['label' => __('messages.encoder.declassified_works'), 'value' => $declassifiedStatusId ? Work::where('status_id', $declassifiedStatusId)->count() : 0],
                ['label' => __('messages.encoder.my_works'), 'value' => Work::where('user_id', auth()->id())->count()],
            ];
            $meta['dashboard_tables'] = [
                [
                    'title' => __('messages.encoder.latest_works'),
                    'columns' => ['id', 'title', 'owner', 'type', 'date'],
                    'rows' => Work::with(['user_owner', 'type'])->latest()->limit(5)->get()->map(fn($w) => [
                        'id' => (string) $w->id,
                        'title' => $w->work_title ?? '-',
                        'owner' => $this->userLabel($w->user_owner),
                        'type' => $w->type ? $this->t($w->type, 'type_name', $locale) : '-',
                        'date' => optional($w->created_at)->format('Y-m-d H:i'),
                    ])->toArray(),
                ],
            ];
        } elseif ($key === 'country') {
            $table['columns'] = ['id', 'pays', 'code_tel', 'langue', 'utilisateurs'];
            $table['rows'] = Country::withCount('users')->latest()->limit(120)->get()->map(fn($c) => ['id' => (string) $c->id, 'pays' => $c->country_name, 'code_tel' => $c->country_phone_code ?? '-', 'langue' => $c->country_lang_code ?? '-', 'utilisateurs' => (string) $c->users_count])->toArray();
        } elseif ($key === 'currency') {
            $table['columns'] = ['id', 'nom', 'acronyme', 'utilisateurs', 'oeuvres', 'abonnements'];
            $table['rows'] = Currency::withCount(['users', 'works', 'subscriptions'])->latest()->limit(120)->get()->map(fn($c) => ['id' => (string) $c->id, 'nom' => $this->t($c, 'currency_name', $locale), 'acronyme' => $c->currency_acronym, 'utilisateurs' => (string) $c->users_count, 'oeuvres' => (string) $c->works_count, 'abonnements' => (string) $c->subscriptions_count])->toArray();
        } elseif ($key === 'currency_rate') {
            $table['columns'] = ['id', 'de', 'vers', 'taux', 'date'];
            $table['rows'] = CurrenciesRate::with(['from_currency', 'to_currency'])->latest()->limit(120)->get()->map(fn($r) => ['id' => (string) $r->id, 'de' => $r->from_currency?->currency_acronym ?? '-', 'vers' => $r->to_currency?->currency_acronym ?? '-', 'taux' => (string) $r->rate, 'date' => optional($r->created_at)->format('Y-m-d H:i')])->toArray();
        } elseif ($key === 'role') {
            $table['columns'] = ['id', 'role', 'tache', 'description', 'utilisateurs'];
            $table['rows'] = Role::withCount('users')->latest()->limit(120)->get()->map(fn($r) => ['id' => (string) $r->id, 'role' => $r->role_name, 'tache' => $r->task ?? '-', 'description' => $r->role_description ?? '-', 'utilisateurs' => (string) $r->users_count])->toArray();
        } elseif ($key === 'group') {
            $table['columns'] = ['id', 'groupe', 'categories', 'types', 'etats'];
            $table['rows'] = Group::withCount(['categories', 'types', 'statuses'])->latest()->limit(120)->get()->map(fn($g) => ['id' => (string) $g->id, 'groupe' => $g->group_name, 'categories' => (string) $g->categories_count, 'types' => (string) $g->types_count, 'etats' => (string) $g->statuses_count])->toArray();
        } elseif ($key === 'group_category') {
            $table['columns'] = ['id', 'categorie', 'groupe', 'oeuvres', 'abonnements'];
            $table['rows'] = Category::with('group')->withCount(['works', 'subscriptions'])->latest()->limit(120)->get()->map(fn($c) => ['id' => (string) $c->id, 'categorie' => $this->t($c, 'category_name', $locale), 'groupe' => $c->group?->group_name ?? '-', 'oeuvres' => (string) $c->works_count, 'abonnements' => (string) $c->subscriptions_count])->toArray();
        } elseif ($key === 'group_type') {
            $table['columns'] = ['id', 'type', 'groupe', 'alias', 'icone'];
            $table['rows'] = Type::with('group')->latest()->limit(120)->get()->map(fn($t) => ['id' => (string) $t->id, 'type' => $this->t($t, 'type_name', $locale), 'groupe' => $t->group?->group_name ?? '-', 'alias' => $t->alias ?? '-', 'icone' => $t->icon ?? '-'])->toArray();
        } elseif ($key === 'group_state') {
            $table['columns'] = ['id', 'etat', 'groupe', 'couleur', 'icone'];
            $table['rows'] = Status::with('group')->latest()->limit(120)->get()->map(fn($s) => ['id' => (string) $s->id, 'etat' => $this->t($s, 'status_name', $locale), 'groupe' => $s->group?->group_name ?? '-', 'couleur' => $s->color ?? '-', 'icone' => $s->icon ?? '-'])->toArray();
        } elseif ($key === 'report_reason') {
            $table['columns'] = ['id', 'motif', 'entite', 'seuil', 'blocage', 'signalements'];
            $table['rows'] = ReportReason::withCount('toxic_contents')->latest()->limit(120)->get()->map(fn($r) => ['id' => (string) $r->id, 'motif' => $this->t($r, 'reason_content', $locale), 'entite' => $r->entity ?? '-', 'seuil' => (string) $r->reports_count, 'blocage' => (string) $r->blocked_for, 'signalements' => (string) $r->toxic_contents_count])->toArray();
        } elseif ($key === 'report_reason_reported') {
            $table['columns'] = ['id', 'motif', 'signaleur', 'cible', 'etat', 'date'];
            $table['rows'] = ToxicContent::with(['report_reason', 'user'])->latest()->limit(120)->get()->map(fn($x) => ['id' => (string) $x->id, 'motif' => $x->report_reason ? $this->t($x->report_reason, 'reason_content', $locale) : '-', 'signaleur' => $this->userLabel($x->user), 'cible' => $x->for_user_id ? 'user#' . $x->for_user_id : ($x->for_work_id ? 'work#' . $x->for_work_id : ($x->for_message_id ? 'message#' . $x->for_message_id : '-')), 'etat' => $x->is_archived ? 'Archive' : ($x->is_unlocked ? 'Debloque' : 'Bloque'), 'date' => optional($x->created_at)->format('Y-m-d H:i')])->toArray();
        } elseif ($key === 'subscription') {
            $table['columns'] = ['id', 'heures', 'prix', 'devise', 'type', 'categorie'];
            $table['rows'] = Subscription::with(['currency', 'type', 'category'])->latest()->limit(120)->get()->map(fn($s) => ['id' => (string) $s->id, 'heures' => (string) ($s->number_of_hours ?? 0), 'prix' => is_null($s->price) ? '-' : (string) $s->price, 'devise' => $s->currency?->currency_acronym ?? '-', 'type' => $s->type ? $this->t($s->type, 'type_name', $locale) : '-', 'categorie' => $s->category ? $this->t($s->category, 'category_name', $locale) : '-'])->toArray();
        } elseif ($key === 'organization') {
            $table['columns'] = ['id', 'nom', 'acronyme', 'email', 'telephone', 'site', 'type', 'etat', 'oeuvres'];
            $table['rows'] = Organization::with(['type', 'status'])->withCount('works')->latest()->limit(120)->get()->map(fn($o) => [
                'id' => (string) $o->id,
                'nom' => (string) ($o->org_name ?? '-'),
                'acronyme' => (string) ($o->org_acronym ?? '-'),
                'email' => (string) ($o->email ?? '-'),
                'telephone' => (string) ($o->phone ?? '-'),
                'site' => (string) ($o->website_url ?? '-'),
                'type' => $o->type ? $this->t($o->type, 'type_name', $locale) : '-',
                'etat' => $o->status ? $this->t($o->status, 'status_name', $locale) : '-',
                'oeuvres' => (string) $o->works_count,
            ])->toArray();
            $meta['work_status_options'] = $this->statusOptionsForPage('work', $locale);
        } elseif (in_array($key, ['work', 'encoder_work'], true)) {
            $table['columns'] = ['id', 'titre', 'auteur', 'type', 'prix', 'devise', 'etat'];
            $table['rows'] = Work::with(['user_owner', 'type', 'currency', 'status'])->latest()->limit(120)->get()->map(fn($w) => [
                'id' => (string) $w->id,
                'titre' => $w->work_title ?? '-',
                'auteur' => $this->userLabel($w->user_owner),
                'type' => $w->type ? $this->t($w->type, 'type_name', $locale) : '-',
                'prix' => is_null($w->consultation_price) ? '-' : (string) $w->consultation_price,
                'devise' => $w->currency?->currency_acronym ?? '-',
                'etat' => $w->status ? $this->t($w->status, 'status_name', $locale) : '-',
                '_work_status_id' => $w->status_id,
                '_status_id' => $w->status_id,
                '_status_name' => $w->status ? $this->t($w->status, 'status_name', $locale) : '-',
                '_status_color' => $this->normalizeStatusColor($w->status?->color),
            ])->toArray();
            $meta['work_status_options'] = $this->statusOptionsForPage('work', $locale);
            $meta['work_form_options'] = $this->workFormOptions($locale);
        } elseif (in_array($key, ['users_partner', 'users_sponsor'])) {
            $partners = Partner::query()
                ->when($key === 'users_partner', fn ($query) => $query->whereNotNull('from_user_id'))
                ->when($key === 'users_sponsor', fn ($query) => $query->whereNotNull('from_organization_id'))
                ->latest()
                ->limit(160)
                ->get();
            $table['columns'] = ['id', 'nom', 'type', 'provenance', 'site_web', 'date'];
            $table['rows'] = $partners->map(function ($partner) use ($key) {
                $source = '-';
                if (!empty($partner->from_user_id)) {
                    $source = $this->userLabel(User::find($partner->from_user_id));
                } elseif (!empty($partner->from_organization_id)) {
                    $org = Organization::find($partner->from_organization_id);
                    $source = trim((string) ($org?->org_name ?? '')) ?: ('Organisation #' . $partner->from_organization_id);
                }

                return [
                    'id' => (string) $partner->id,
                    'nom' => $partner->name ?? '-',
                    'type' => $key === 'users_sponsor' ? __('messages.nav.sponsor') : __('messages.nav.partner'),
                    'provenance' => $source,
                    'site_web' => $partner->website_url ?? '-',
                    'date' => optional($partner->created_at)->format('Y-m-d H:i'),
                ];
            })->toArray();
        } elseif (in_array($key, ['users', 'users_admin', 'users_manager', 'users_member', 'users_publisher'])) {
            if ($key === 'users') {
                $users = User::with(['roles', 'country', 'status'])->where('id', '<>', auth()->id())->latest()->limit(160)->get();
            } elseif ($key === 'users_member') {
                $users = $this->usersWithOnlyRole(['membre', 'member'])->where('id', '<>', auth()->id())->latest()->limit(160)->get();
            } else {
                $users = $this->usersByRole($key === 'users_admin' ? ['admin', 'administrator'] : ($key === 'users_manager' ? ['manager'] : ['publisher', 'publieur']))->where('id', '<>', auth()->id())->latest()->limit(160)->get();
            }
            $table = $this->userRows($users, $locale);
            if ($key === 'users') {
                $meta['role_options'] = Role::orderBy('role_name')->pluck('role_name')->toArray();
            }
            $meta['role_select_options'] = Role::orderBy('id')->pluck('role_name', 'id')->toArray();
            $meta['status_select_options'] = $this->statusOptionsForPage('users', $locale);
            $meta['work_status_options'] = $this->statusOptionsForPage('work', $locale);
            if ($key === 'users_partner') $meta['quick_links'] = [['label' => 'Codes d activation', 'route' => 'admin.users.entity.section.home', 'params' => ['entity' => 'partner', 'id' => $payload['selectedId'] ?? 0, 'section' => 'activation-codes']], ['label' => 'Membres', 'route' => 'admin.users.entity.section.home', 'params' => ['entity' => 'partner', 'id' => $payload['selectedId'] ?? 0, 'section' => 'members']]];
            if ($key === 'users_publisher') $meta['quick_links'] = [['label' => 'Oeuvres', 'route' => 'admin.users.entity.section.home', 'params' => ['entity' => 'publisher', 'id' => $payload['selectedId'] ?? 0, 'section' => 'works']], ['label' => 'Abonnes', 'route' => 'admin.users.entity.section.home', 'params' => ['entity' => 'publisher', 'id' => $payload['selectedId'] ?? 0, 'section' => 'members']]];
        } elseif ($key === 'users_partner_activation_codes') {
            $id = (int) ($payload['selectedId'] ?? 0); $table['columns'] = ['id', 'code', 'actif', 'partenaire', 'utilisateur', 'date'];
            $table['rows'] = ActivationCode::with('user')->where(function (Builder $q) use ($id) { $q->where('for_partner_id', $id)->orWhere('user_id', $id); })->latest()->limit(160)->get()->map(fn($a) => ['id' => (string) $a->id, 'code' => $a->code, 'actif' => $a->is_active ? 'Oui' : 'Non', 'partenaire' => (string) ($a->for_partner_id ?? '-'), 'utilisateur' => $this->userLabel($a->user), 'date' => optional($a->created_at)->format('Y-m-d H:i')])->toArray();
        } elseif ($key === 'users_partner_members') {
            $table = $this->userRows(User::with(['roles', 'country', 'status'])->whereNotNull('promo_code')->orWhere('is_promoted', 1)->latest()->limit(160)->get(), $locale);
            $meta['role_select_options'] = Role::orderBy('id')->pluck('role_name', 'id')->toArray();
            $meta['status_select_options'] = $this->statusOptionsForPage('users', $locale);
            $meta['work_status_options'] = $this->statusOptionsForPage('work', $locale);
        } elseif ($key === 'users_publisher_works') {
            $id = (int) ($payload['selectedId'] ?? 0); $table['columns'] = ['id', 'titre', 'type', 'prix', 'devise', 'date'];
            $table['rows'] = Work::with(['type', 'currency'])->where('user_id', $id)->latest()->limit(160)->get()->map(fn($w) => ['id' => (string) $w->id, 'titre' => $w->work_title ?? '-', 'type' => $w->type ? $this->t($w->type, 'type_name', $locale) : '-', 'prix' => is_null($w->consultation_price) ? '-' : (string) $w->consultation_price, 'devise' => $w->currency?->currency_acronym ?? '-', 'date' => optional($w->created_at)->format('Y-m-d H:i')])->toArray();
        } elseif ($key === 'users_publisher_members') {
            $id = (int) ($payload['selectedId'] ?? 0); $table['columns'] = ['id', 'membre', 'email', 'ville', 'nombre_achats'];
            $table['rows'] = DB::table('cart_work')->join('carts', 'cart_work.cart_id', '=', 'carts.id')->join('users', 'carts.user_id', '=', 'users.id')->join('works', 'cart_work.work_id', '=', 'works.id')->where('works.user_id', $id)->select('users.id', 'users.firstname', 'users.lastname', 'users.email', 'users.city', DB::raw('COUNT(*) AS purchases'))->groupBy('users.id', 'users.firstname', 'users.lastname', 'users.email', 'users.city')->orderByDesc('purchases')->limit(160)->get()->map(fn($r) => ['id' => (string) $r->id, 'membre' => trim(($r->firstname ?? '') . ' ' . ($r->lastname ?? '')) ?: ('User #' . $r->id), 'email' => $r->email ?? '-', 'ville' => $r->city ?? '-', 'nombre_achats' => (string) $r->purchases])->toArray();
            $meta['work_status_options'] = $this->statusOptionsForPage('work', $locale);
        } elseif ($key === 'notifications') {
            $notifications = Notification::with(['from_user', 'type', 'work', 'like.for_work'])
                ->where('to_user_id', auth()->id())
                ->whereHas('type', fn (Builder $q) => $q->whereRaw("RIGHT(alias, 6) = '_notif'"))
                ->latest()
                ->limit(220)
                ->get();
            $meta['notification_feed'] = $notifications->map(function ($n) {
                $fromName = $this->userLabel($n->from_user);
                $alias = $n->type?->alias;
                return [
                    'id' => $n->id,
                    'from' => $fromName,
                    'text' => ($alias && Lang::has('notifications.' . $alias)) ? __('notifications.' . $alias, ['from_user_names' => $fromName]) : ($fromName . ' ' . ($n->type ? $this->t($n->type, 'type_name', app()->getLocale()) : __('messages.notifications.default'))),
                    'date' => optional($n->created_at)->diffForHumans(),
                    'created_at' => optional($n->created_at)->format('Y-m-d H:i'),
                    'icon' => $n->type?->icon ?: 'feather-bell',
                    'view_url' => $this->notificationTargetUrl($n),
                ];
            })->values()->toArray();
        } elseif ($key === 'messages') {
            $table['columns'] = ['id', 'expediteur', 'destinataire', 'contenu', 'date'];
            $table['rows'] = Message::with(['user', 'addressee_user'])->latest()->limit(220)->get()->map(function ($m) { $txt = trim((string) $m->message_content); return ['id' => (string) $m->id, 'expediteur' => $this->userLabel($m->user), 'destinataire' => $this->userLabel($m->addressee_user), 'contenu' => mb_strlen($txt) > 80 ? mb_substr($txt, 0, 80) . '...' : ($txt !== '' ? $txt : '-'), 'date' => optional($m->created_at)->format('Y-m-d H:i')]; })->toArray();
        }
        if ($q !== '' && !empty($table['rows'])) {
            $q = mb_strtolower($q);
            $table['rows'] = array_values(array_filter($table['rows'], fn($r) => collect($r)->contains(fn($v) => mb_stripos((string) $v, $q) !== false)));
        }

        if (!empty($table['rows'])) {
            $table['rows'] = $this->attachRowActions($key, $table['rows']);
            [$table['rows'], $meta['pagination']] = $this->paginateRows($table['rows'], 20, $request?->integer('page', 1) ?? 1);
            $table = $this->applyDisplayIndex($table, $meta['pagination'] ?? null);
        }

        if (!empty($payload['selectedId'])) {
            $meta['detail'] = $this->detailForPage($key, (int) $payload['selectedId'], $locale, $payload);
        }

        return [$cards, $table, $meta];
    }

    private function usersByRole(array $keywords)
    {
        return User::whereHas('roles', function (Builder $q) use ($keywords) {
            foreach ($keywords as $i => $k) { $i === 0 ? $q->whereRaw('LOWER(role_name) LIKE ?', ['%' . mb_strtolower($k) . '%']) : $q->orWhereRaw('LOWER(role_name) LIKE ?', ['%' . mb_strtolower($k) . '%']); }
        })->with(['roles', 'country', 'status']);
    }

    private function usersWithOnlyRole(array $keywords)
    {
        $roleNames = array_map(fn ($keyword) => mb_strtolower($keyword), $keywords);

        return User::whereHas('roles', function (Builder $q) use ($keywords) {
            foreach ($keywords as $i => $k) { $i === 0 ? $q->whereRaw('LOWER(role_name) = ?', [mb_strtolower($k)]) : $q->orWhereRaw('LOWER(role_name) = ?', [mb_strtolower($k)]); }
        })->whereDoesntHave('roles', function (Builder $q) use ($roleNames) {
            $q->whereNotIn(DB::raw('LOWER(role_name)'), $roleNames);
        })->with(['roles', 'country', 'status']);
    }

    private function userRows($users, string $locale): array
    {
        return [
            'columns' => ['id', 'photo', 'nom', 'email', 'ville', 'role', 'pays', 'etat'],
            'rows' => $users->map(function ($u) use ($locale) {
                $roles = $u->relationLoaded('roles') ? $u->roles : $u->roles()->get();
                $currentRole = $roles->sortByDesc(function ($role) {
                    return $role->pivot?->created_at ?? $role->id;
                })->first();
                return [
                    'id' => (string) $u->id,
                    'photo' => $this->normalizeImageUrl($u->avatar_url),
                    'nom' => $this->userLabel($u),
                    'email' => $u->email ?? '-',
                    'ville' => $u->city ?? '-',
                    'role' => $currentRole?->role_name ?? '-',
                    'pays' => $u->country?->country_name ?? '-',
                    'etat' => $u->status ? $this->t($u->status, 'status_name', $locale) : '-',
                    '_role_id' => $currentRole?->id,
                    '_status_id' => $u->status_id,
                    '_status_name' => $u->status ? $this->t($u->status, 'status_name', $locale) : '-',
                    '_status_color' => $this->normalizeStatusColor($u->status?->color),
                ];
            })->toArray()
        ];
    }

    private function formForPage(string $key): ?array
    {
        $base = ['method' => 'POST', 'submit_label' => __('messages.actions.save')];
        return match ($key) {
            'country' => $base + ['title' => __('messages.forms.country.title'), 'action' => route('admin.country.home'), 'fields' => [['name' => 'country_name', 'label' => __('messages.forms.country.country_name'), 'type' => 'text', 'required' => true], ['name' => 'country_phone_code', 'label' => __('messages.forms.country.country_phone_code'), 'type' => 'text'], ['name' => 'country_lang_code', 'label' => __('messages.forms.country.country_lang_code'), 'type' => 'text']]],
            'currency' => $base + ['title' => __('messages.forms.currency.title'), 'action' => route('admin.currency.home'), 'fields' => [['name' => 'currency_name', 'label' => __('messages.forms.currency.currency_name'), 'type' => 'text', 'required' => true], ['name' => 'currency_acronym', 'label' => __('messages.forms.currency.currency_acronym'), 'type' => 'text', 'required' => true], ['name' => 'currency_icon', 'label' => __('messages.forms.currency.currency_icon'), 'type' => 'text']]],
            'currency_rate' => $base + ['title' => __('messages.forms.currency_rate.title'), 'action' => route('admin.currency.entity.home', ['entity' => 'rate']), 'fields' => [['name' => 'from_currency_id', 'label' => __('messages.forms.currency_rate.from_currency_id'), 'type' => 'number', 'required' => true], ['name' => 'to_currency_id', 'label' => __('messages.forms.currency_rate.to_currency_id'), 'type' => 'number', 'required' => true], ['name' => 'rate', 'label' => __('messages.forms.currency_rate.rate'), 'type' => 'number', 'step' => '0.00001', 'required' => true]]],
            'role' => $base + ['title' => __('messages.forms.role.title'), 'action' => route('admin.role.home'), 'fields' => [['name' => 'role_name', 'label' => __('messages.forms.role.role_name'), 'type' => 'text', 'required' => true], ['name' => 'task', 'label' => __('messages.forms.role.task'), 'type' => 'text'], ['name' => 'role_description', 'label' => __('messages.forms.role.role_description'), 'type' => 'textarea']]],
            'group' => $base + ['title' => __('messages.forms.group.title'), 'action' => route('admin.group.home'), 'fields' => [['name' => 'group_name', 'label' => __('messages.forms.group.group_name'), 'type' => 'text', 'required' => true], ['name' => 'group_description', 'label' => __('messages.forms.group.group_description'), 'type' => 'textarea']]],
            'report_reason' => $base + ['title' => __('messages.forms.report_reason.title'), 'action' => route('admin.report_reason.home'), 'fields' => [['name' => 'reason_content', 'label' => __('messages.forms.report_reason.reason_content'), 'type' => 'text', 'required' => true], ['name' => 'reports_count', 'label' => __('messages.forms.report_reason.reports_count'), 'type' => 'number', 'required' => true], ['name' => 'blocked_for', 'label' => __('messages.forms.report_reason.blocked_for'), 'type' => 'number', 'required' => true], ['name' => 'entity', 'label' => __('messages.forms.report_reason.entity'), 'type' => 'text']]],
            'subscription' => $base + ['title' => __('messages.forms.subscription.title'), 'action' => route('admin.subscription.home'), 'fields' => [['name' => 'number_of_hours', 'label' => __('messages.forms.subscription.number_of_hours'), 'type' => 'number'], ['name' => 'price', 'label' => __('messages.forms.subscription.price'), 'type' => 'number', 'step' => '0.01'], ['name' => 'currency_id', 'label' => __('messages.forms.subscription.currency_id'), 'type' => 'number'], ['name' => 'type_id', 'label' => __('messages.forms.subscription.type_id'), 'type' => 'number'], ['name' => 'category_id', 'label' => __('messages.forms.subscription.category_id'), 'type' => 'number']]],
            'organization' => $base + ['title' => __('messages.forms.organization.title'), 'action' => route('admin.organizations.home'), 'fields' => [['name' => 'org_name', 'label' => __('messages.forms.organization.org_name'), 'type' => 'text', 'required' => true], ['name' => 'org_acronym', 'label' => __('messages.forms.organization.org_acronym'), 'type' => 'text'], ['name' => 'email', 'label' => __('messages.forms.organization.email'), 'type' => 'email'], ['name' => 'phone', 'label' => __('messages.forms.organization.phone'), 'type' => 'text'], ['name' => 'website_url', 'label' => __('messages.forms.organization.website_url'), 'type' => 'text']]],
            'work' => $base + ['title' => __('messages.forms.work.title'), 'action' => route('admin.work.home'), 'fields' => [['name' => 'work_title', 'label' => __('messages.forms.work.work_title'), 'type' => 'text', 'required' => true], ['name' => 'type_id', 'label' => __('messages.forms.work.type_id'), 'type' => 'number', 'required' => true], ['name' => 'user_id', 'label' => __('messages.forms.work.user_id'), 'type' => 'number'], ['name' => 'author', 'label' => __('messages.forms.work.author'), 'type' => 'text']]],
            'encoder_work' => $base + ['title' => __('messages.forms.work.title'), 'action' => route('encoder.work.home'), 'fields' => [['name' => 'work_title', 'label' => __('messages.forms.work.work_title'), 'type' => 'text', 'required' => true]]],
            'users_partner' => $base + ['title' => __('messages.forms.partner.title'), 'action' => route('admin.users.entity.home', ['entity' => 'partner']), 'fields' => [['name' => 'name', 'label' => __('messages.forms.partner.name'), 'type' => 'text', 'required' => true], ['name' => 'message', 'label' => __('messages.forms.partner.message'), 'type' => 'textarea'], ['name' => 'from_user_id', 'label' => __('messages.forms.partner.from_user_id'), 'type' => 'autocomplete', 'lookup' => route('admin.lookup', ['entity' => 'users']), 'required' => true], ['name' => 'from_organization_id', 'label' => __('messages.forms.partner.from_organization_id'), 'type' => 'autocomplete', 'lookup' => route('admin.lookup', ['entity' => 'organizations']), 'required' => true], ['name' => 'image_url', 'label' => __('messages.forms.partner.image_url'), 'type' => 'text'], ['name' => 'website_url', 'label' => __('messages.forms.partner.website_url'), 'type' => 'url']]],
            'users_sponsor' => $base + ['title' => __('messages.forms.sponsor.title'), 'action' => route('admin.users.entity.home', ['entity' => 'sponsor']), 'fields' => [['name' => 'name', 'label' => __('messages.forms.partner.name'), 'type' => 'text', 'required' => true], ['name' => 'message', 'label' => __('messages.forms.partner.message'), 'type' => 'textarea'], ['name' => 'from_user_id', 'label' => __('messages.forms.partner.from_user_id'), 'type' => 'autocomplete', 'lookup' => route('admin.lookup', ['entity' => 'users']), 'required' => true], ['name' => 'from_organization_id', 'label' => __('messages.forms.partner.from_organization_id'), 'type' => 'autocomplete', 'lookup' => route('admin.lookup', ['entity' => 'organizations']), 'required' => true], ['name' => 'image_url', 'label' => __('messages.forms.partner.image_url'), 'type' => 'text'], ['name' => 'website_url', 'label' => __('messages.forms.partner.website_url'), 'type' => 'url']]],
            default => null,
        };
    }

    private function t($model, string $field, string $locale): string
    {
        if (method_exists($model, 'getTranslation')) { try { $v = $model->getTranslation($field, $locale, false); if (!empty($v)) return (string) $v; } catch (\Throwable $th) {} }
        $raw = $model->{$field} ?? null; if (is_array($raw) && isset($raw[$locale])) return (string) $raw[$locale];
        if (is_string($raw)) { $d = json_decode($raw, true); if (is_array($d) && isset($d[$locale])) return (string) $d[$locale]; return $raw; }
        return '-';
    }

    private function userLabel($user): string
    {
        if (empty($user)) return '-';
        return trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: ($user->surname ?? ($user->username ?? ($user->email ?? ('User #' . $user->id))));
    }

    private function detailForPage(string $pageKey, int $id, string $locale, array $payload = []): ?array
    {
        $model = $this->resolveModelForPage($pageKey, $id, $payload);

        if (!$model) return null;

        if ($model instanceof User) {
            $items = $this->scalarDetailItems($model, ['remember_token']);
            $items['Roles'] = $model->roles->pluck('role_name')->implode(', ') ?: '-';
            $items['Etat'] = $model->status ? $this->t($model->status, 'status_name', $locale) : '-';
            $works = $this->detailWorkRows(
                Work::with(['status', 'type', 'currency'])->where('user_id', $model->id)->latest()->get(),
                $locale
            );
            return [
                'title' => __('messages.pages.users') . ' #' . $id,
                'items' => $items,
                'sections' => [
                    ['kind' => 'works', 'title' => 'Oeuvres de cet utilisateur', 'rows' => $works],
                ],
            ];
        }

        if ($model instanceof Work) {
            $items = $this->scalarDetailItems($model);
            $items['Type'] = $model->type ? $this->t($model->type, 'type_name', $locale) : '-';
            $items['Etat'] = $model->status ? $this->t($model->status, 'status_name', $locale) : '-';
            $items['Devise'] = $model->currency?->currency_acronym ?? '-';
            $items['Publieur'] = $this->userLabel($model->user_owner);
            if (!is_null($model->media_length)) {
                $seconds = (int) $model->media_length;
                $items['Duree'] = sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
            }
            $categories = $model->categories->map(fn ($c) => $this->t($c, 'category_name', $locale))->filter()->values()->toArray();
            $files = $model->files()->with('type')->latest()->get()->map(function ($f) use ($locale) {
                return [
                    'name' => trim((string) ($f->file_name ?? '-')),
                    'type' => $f->type ? $this->t($f->type, 'type_name', $locale) : '-',
                    'url' => (string) ($f->file_url ?? ''),
                ];
            })->values()->toArray();
            return [
                'title' => __('messages.nav.work') . ' #' . $id,
                'items' => $items,
                'sections' => [
                    ['kind' => 'list', 'title' => 'Categories', 'rows' => $categories],
                    ['kind' => 'files', 'title' => 'Fichiers associes', 'rows' => $files],
                ],
            ];
        }

        if ($model instanceof Organization) {
            $items = $this->scalarDetailItems($model);
            $items['Type'] = $model->type ? $this->t($model->type, 'type_name', $locale) : '-';
            $items['Etat'] = $model->status ? $this->t($model->status, 'status_name', $locale) : '-';
            $items['Proprietaire'] = $this->userLabel($model->user_owner);
            $works = $this->detailWorkRows($model->works, $locale);
            return [
                'title' => 'Organisation #' . $id,
                'items' => $items,
                'sections' => [
                    ['kind' => 'works', 'title' => 'Oeuvres de cette organisation', 'rows' => $works],
                ],
            ];
        }

        if ($model instanceof Partner) {
            $items = $this->scalarDetailItems($model);
            $items['Utilisateur'] = !empty($model->from_user_id) ? $this->userLabel(User::find($model->from_user_id)) : '-';
            if (!empty($model->from_organization_id)) {
                $org = Organization::find($model->from_organization_id);
                $items['Organisation'] = trim((string) ($org?->org_name ?? '')) ?: ('Organisation #' . $model->from_organization_id);
            } else {
                $items['Organisation'] = '-';
            }

            return [
                'title' => ($pageKey === 'users_sponsor' ? __('messages.nav.sponsor') : __('messages.nav.partner')) . ' #' . $id,
                'items' => $items,
            ];
        }

        $items = [];
        foreach ($model->toArray() as $k => $v) {
            if (is_scalar($v) || is_null($v)) {
                $items[strtoupper($k)] = (string) ($v ?? '-');
            }
        }

        return ['title' => 'ID #' . $id, 'items' => array_slice($items, 0, 12, true)];
    }

    private function scalarDetailItems($model, array $exclude = []): array
    {
        $items = [];
        foreach ($model->toArray() as $k => $v) {
            if (in_array($k, $exclude, true)) continue;
            if (is_array($v)) continue;
            if (is_scalar($v) || is_null($v)) {
                $label = strtoupper(str_replace('_', ' ', $k));
                $items[$label] = (string) ($v ?? '-');
            }
        }
        return $items;
    }

    private function detailWorkRows($works, string $locale): array
    {
        return collect($works)->map(function ($w) use ($locale) {
            return [
                'id' => (int) $w->id,
                'title' => (string) ($w->work_title ?? '-'),
                'type' => $w->type ? $this->t($w->type, 'type_name', $locale) : '-',
                'price' => is_null($w->consultation_price) ? '-' : (string) $w->consultation_price,
                'currency' => (string) ($w->currency?->currency_acronym ?? '-'),
                '_status_id' => $w->status_id,
                '_status_name' => $w->status ? $this->t($w->status, 'status_name', $locale) : '-',
                '_status_color' => $this->normalizeStatusColor($w->status?->color),
                '_view_url' => route('admin.work.datas', ['id' => (int) $w->id]),
            ];
        })->values()->toArray();
    }

    private function resolveModelForPage(string $pageKey, int $id, array $payload = [])
    {
        return match ($pageKey) {
            'country' => Country::find($id),
            'currency' => Currency::find($id),
            'currency_rate' => CurrenciesRate::with(['from_currency', 'to_currency'])->find($id),
            'role' => Role::find($id),
            'group' => Group::find($id),
            'group_category' => Category::with('group')->find($id),
            'group_type' => Type::with('group')->find($id),
            'group_state' => Status::with('group')->find($id),
            'report_reason' => ReportReason::find($id),
            'report_reason_reported' => ToxicContent::with(['report_reason', 'user'])->find($id),
            'subscription' => Subscription::with(['currency', 'type', 'category'])->find($id),
            'work', 'encoder_work', 'users_publisher_works' => Work::with(['user_owner', 'type', 'status', 'currency', 'categories'])->find($id),
            'organization' => Organization::with(['type', 'status', 'user_owner', 'works.status', 'works.type', 'works.currency'])->find($id),
            'users', 'users_admin', 'users_manager', 'users_member', 'users_publisher', 'users_partner_members', 'users_publisher_members' => User::with(['roles', 'country', 'status'])->find($id),
            'users_partner', 'users_sponsor' => Partner::find($id),
            'users_partner_activation_codes' => ActivationCode::with('user')->find($id),
            default => null,
        };
    }

    private function paginateRows(array $rows, int $perPage, int $currentPage): array
    {
        $total = count($rows);
        $currentPage = max(1, $currentPage);
        $offset = ($currentPage - 1) * $perPage;
        $items = array_slice($rows, $offset, $perPage);

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return [$items, $paginator];
    }

    private function attachRowActions(string $pageKey, array $rows): array
    {
        return array_map(function (array $row) use ($pageKey) {
            $id = $row['id'] ?? null;
            if (empty($id)) return $row;

            $row['_view_url'] = $this->viewUrlForPage($pageKey, (int) $id);
            $entity = $this->deleteEntityForPage($pageKey);
            $row['_delete_url'] = $entity ? route('admin.data.delete', ['entity' => $entity, 'id' => (int) $id]) : null;

            return $row;
        }, $rows);
    }

    private function applyDisplayIndex(array $table, ?LengthAwarePaginator $pagination = null): array
    {
        if (empty($table['columns']) || empty($table['rows'])) return $table;
        $idColPos = array_search('id', $table['columns'], true);
        if ($idColPos === false) return $table;

        $table['columns'][$idColPos] = 'index';
        $offset = 0;
        if (!empty($pagination)) {
            $offset = max(0, ($pagination->currentPage() - 1) * $pagination->perPage());
        }

        foreach ($table['rows'] as $i => $row) {
            $table['rows'][$i]['_id'] = $row['id'] ?? null;
            $table['rows'][$i]['index'] = (string) ($offset + $i + 1);
        }

        return $table;
    }

    private function viewUrlForPage(string $pageKey, int $id): ?string
    {
        return match ($pageKey) {
            'country' => route('admin.country.datas', ['id' => $id]),
            'currency' => route('admin.currency.datas', ['id' => $id]),
            'currency_rate' => route('admin.currency.entity.datas', ['entity' => 'rate', 'id' => $id]),
            'role' => route('admin.role.datas', ['id' => $id]),
            'group' => route('admin.group.datas', ['id' => $id]),
            'group_category' => route('admin.group.entity.datas', ['entity' => 'category', 'id' => $id]),
            'group_type' => route('admin.group.entity.datas', ['entity' => 'type', 'id' => $id]),
            'group_state' => route('admin.group.entity.datas', ['entity' => 'state', 'id' => $id]),
            'report_reason' => route('admin.report_reason.datas', ['id' => $id]),
            'report_reason_reported' => route('admin.report_reason.entity.datas', ['entity' => 'reported', 'id' => $id]),
            'subscription' => route('admin.subscription.datas', ['id' => $id]),
            'organization' => route('admin.organizations.datas', ['id' => $id]),
            'work' => route('admin.work.datas', ['id' => $id]),
            'encoder_work' => route('encoder.work.datas', ['id' => $id]),
            'users', 'users_entity' => route('admin.users.entity.datas', ['entity' => 'member', 'id' => $id]),
            'users_admin' => route('admin.users.entity.datas', ['entity' => 'admin', 'id' => $id]),
            'users_manager' => route('admin.users.entity.datas', ['entity' => 'manager', 'id' => $id]),
            'users_member' => route('admin.users.entity.datas', ['entity' => 'member', 'id' => $id]),
            'users_partner' => route('admin.users.entity.datas', ['entity' => 'partner', 'id' => $id]),
            'users_sponsor' => route('admin.users.entity.datas', ['entity' => 'sponsor', 'id' => $id]),
            'users_publisher' => route('admin.users.entity.datas', ['entity' => 'publisher', 'id' => $id]),
            'users_partner_activation_codes' => route('admin.users.entity.section.home', ['entity' => 'partner', 'id' => $id, 'section' => 'activation-codes']),
            'users_partner_members' => route('admin.users.entity.section.home', ['entity' => 'partner', 'id' => $id, 'section' => 'members']),
            'users_publisher_works' => route('admin.users.entity.section.home', ['entity' => 'publisher', 'id' => $id, 'section' => 'works']),
            'users_publisher_members' => route('admin.users.entity.section.home', ['entity' => 'publisher', 'id' => $id, 'section' => 'members']),
            'notifications' => route('admin.notifications.home', ['focus' => $id]),
            default => null,
        };
    }

    private function updateUrlForPage(string $pageKey, int $id, array $payload = []): ?string
    {
        return match ($pageKey) {
            'country' => url('/admin/country/' . $id),
            'currency' => url('/admin/currency/' . $id),
            'currency_rate' => url('/admin/currency/rate/' . $id),
            'role' => url('/admin/role/' . $id),
            'group' => url('/admin/group/' . $id),
            'group_category' => url('/admin/group/category/' . $id),
            'group_type' => url('/admin/group/type/' . $id),
            'group_state' => url('/admin/group/state/' . $id),
            'report_reason' => url('/admin/report-reason/' . $id),
            'report_reason_reported' => url('/admin/report-reason/reported/' . $id),
            'subscription' => url('/admin/subscription/' . $id),
            'organization' => url('/admin/organizations/' . $id),
            'work' => url('/admin/work/' . $id),
            'encoder_work' => url('/encoder/work/' . $id),
            default => null,
        };
    }

    private function listUrlForPage(string $pageKey, array $payload = []): ?string
    {
        return match ($pageKey) {
            'country' => route('admin.country.home'),
            'currency' => route('admin.currency.home'),
            'currency_rate' => route('admin.currency.entity.home', ['entity' => 'rate']),
            'role' => route('admin.role.home'),
            'group' => route('admin.group.home'),
            'group_category' => route('admin.group.entity.home', ['entity' => 'category']),
            'group_type' => route('admin.group.entity.home', ['entity' => 'type']),
            'group_state' => route('admin.group.entity.home', ['entity' => 'state']),
            'report_reason' => route('admin.report_reason.home'),
            'report_reason_reported' => route('admin.report_reason.entity.home', ['entity' => 'reported']),
            'subscription' => route('admin.subscription.home'),
            'organization' => route('admin.organizations.home'),
            'work' => route('admin.work.home'),
            'encoder_work' => route('encoder.work.home'),
            'users' => route('admin.users.home'),
            'users_admin' => route('admin.users.entity.home', ['entity' => 'admin']),
            'users_manager' => route('admin.users.entity.home', ['entity' => 'manager']),
            'users_member' => route('admin.users.entity.home', ['entity' => 'member']),
            'users_partner' => route('admin.users.entity.home', ['entity' => 'partner']),
            'users_sponsor' => route('admin.users.entity.home', ['entity' => 'sponsor']),
            'users_publisher' => route('admin.users.entity.home', ['entity' => 'publisher']),
            'users_partner_activation_codes' => route('admin.users.entity.section.home', ['entity' => 'partner', 'id' => (int) ($payload['selectedId'] ?? 0), 'section' => 'activation-codes']),
            'users_partner_members' => route('admin.users.entity.section.home', ['entity' => 'partner', 'id' => (int) ($payload['selectedId'] ?? 0), 'section' => 'members']),
            'users_publisher_works' => route('admin.users.entity.section.home', ['entity' => 'publisher', 'id' => (int) ($payload['selectedId'] ?? 0), 'section' => 'works']),
            'users_publisher_members' => route('admin.users.entity.section.home', ['entity' => 'publisher', 'id' => (int) ($payload['selectedId'] ?? 0), 'section' => 'members']),
            default => null,
        };
    }

    private function backUrlForPage(string $pageKey, array $payload = [], ?Request $request = null): ?string
    {
        $previous = url()->previous();
        if (!empty($previous) && $previous !== $request?->fullUrl() && (str_contains($previous, '/admin/') || str_contains($previous, '/encoder/'))) {
            return $previous;
        }
        return $this->listUrlForPage($pageKey, $payload);
    }

    private function deleteEntityForPage(string $pageKey): ?string
    {
        return match ($pageKey) {
            'country' => 'country',
            'currency' => 'currency',
            'currency_rate' => 'currencies_rate',
            'role' => 'role',
            'group' => 'group',
            'group_category' => 'category',
            'group_type' => 'type',
            'group_state' => 'status',
            'report_reason' => 'report_reason',
            'report_reason_reported' => 'toxic_content',
            'subscription' => 'subscription',
            'organization' => 'organization',
            'work', 'users_publisher_works' => 'work',
            'users_partner', 'users_sponsor' => 'partner',
            'users_partner_activation_codes' => 'activation_code',
            'notifications' => 'notification',
            default => null,
        };
    }

    private function normalizeImageUrl(?string $value): ?string
    {
        if (empty($value)) return null;
        if (Str::startsWith($value, ['http://', 'https://', '//', 'data:'])) return $value;
        if (Str::startsWith($value, '/storage/')) return 'https://boongo7.com' . $value;
        if (Str::startsWith($value, 'storage/')) return 'https://boongo7.com/' . $value;
        return asset('storage/' . ltrim($value, '/'));
    }

    private function notificationTargetUrl(Notification $notification): string
    {
        if (!empty($notification->work_id)) {
            return route('admin.work.datas', ['id' => $notification->work_id]);
        }

        if ($notification->relationLoaded('like') && $notification->like && !empty($notification->like->for_work_id)) {
            return route('admin.work.datas', ['id' => $notification->like->for_work_id]);
        }

        if (!empty($notification->event_id) || !empty($notification->circle_id)) {
            return route('admin.users.entity.datas', ['entity' => 'member', 'id' => (int) $notification->from_user_id]);
        }

        return route('admin.notifications.home', ['focus' => $notification->id]);
    }

    public function deleteEntity(string $entity, int $id, Request $request)
    {
        $models = [
            'country' => Country::class,
            'currency' => Currency::class,
            'currencies_rate' => CurrenciesRate::class,
            'role' => Role::class,
            'group' => Group::class,
            'category' => Category::class,
            'type' => Type::class,
            'status' => Status::class,
            'report_reason' => ReportReason::class,
            'toxic_content' => ToxicContent::class,
            'subscription' => Subscription::class,
            'organization' => Organization::class,
            'work' => Work::class,
            'partner' => Partner::class,
            'activation_code' => ActivationCode::class,
            'notification' => Notification::class,
        ];

        $class = $models[$entity] ?? null;
        if (!$class || !is_subclass_of($class, Model::class)) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 404);
        }

        $item = $class::find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 404);
        }

        $item->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return Redirect::back()->with('success_message', __('messages.labels.deleted'));
    }

    public function updateUserRole(Request $request, int $id)
    {
        $v = Validator::make($request->all(), ['role_id' => 'required|integer|exists:roles,id']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());

        $user = User::with('roles')->findOrFail($id);
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 422);
        }
        $user->roles()->sync([$v->validated()['role_id']]);
        return response()->json(['success' => true]);
    }

    public function updateUserStatus(Request $request, int $id)
    {
        $v = Validator::make($request->all(), ['status_id' => 'required|integer|exists:statuses,id']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $statusId = (int) $v->validated()['status_id'];
        if (!$this->isAllowedStatusForPage($statusId, 'users')) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 422);
        }

        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 422);
        }
        $user->status_id = $statusId;
        $user->save();
        return response()->json(['success' => true]);
    }

    public function updateWorkStatus(Request $request, int $id)
    {
        $v = Validator::make($request->all(), ['status_id' => 'required|integer|exists:statuses,id']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $statusId = (int) $v->validated()['status_id'];
        if (!$this->isAllowedStatusForPage($statusId, 'work')) {
            return response()->json(['success' => false, 'message' => __('messages.errors.generic')], 422);
        }
        $work = Work::findOrFail($id);
        $work->status_id = $statusId;
        $work->save();
        return response()->json(['success' => true]);
    }

    private function statusOptionsForPage(string $pageKey, string $locale): array
    {
        $groupId = $this->statusGroupIdForPage($pageKey);
        $query = Status::query()->orderBy('id');
        if (!empty($groupId)) {
            $query->where('group_id', $groupId);
        }

        return $query->get()->mapWithKeys(function (Status $s) use ($locale) {
            return [
                $s->id => [
                    'label' => $this->t($s, 'status_name', $locale),
                    'color' => $this->normalizeStatusColor($s->color),
                ],
            ];
        })->toArray();
    }

    private function isAllowedStatusForPage(int $statusId, string $pageKey): bool
    {
        $groupId = $this->statusGroupIdForPage($pageKey);
        if (empty($groupId)) return Status::whereKey($statusId)->exists();
        return Status::whereKey($statusId)->where('group_id', $groupId)->exists();
    }

    private function statusGroupIdForPage(string $pageKey): ?int
    {
        $needles = match ($pageKey) {
            'work' => ['etat de l oeuvre', 'etat oeuvre'],
            'users', 'users_admin', 'users_manager', 'users_member', 'users_partner', 'users_sponsor', 'users_publisher' => ['etat de l utilisateur', 'etat utilisateur'],
            default => [],
        };

        return $this->findGroupIdByKeywords($needles);
    }

    private function findGroupIdByKeywords(array $needles): ?int
    {
        if (empty($needles)) return null;
        $groups = Group::query()->select(['id', 'group_name'])->get();
        foreach ($groups as $group) {
            $name = Str::of((string) $group->group_name)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value();
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) return (int) $group->id;
            }
        }

        return null;
    }

    private function findStatusIdByNameNeedles(array $needles, ?int $groupId = null): ?int
    {
        $statuses = Status::query()->select(['id', 'status_name', 'group_id'])->when($groupId, fn ($q) => $q->where('group_id', $groupId))->get();
        foreach ($statuses as $status) {
            $name = Str::of($this->t($status, 'status_name', 'fr'))->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value();
            foreach ($needles as $needle) {
                if (str_contains($name, $needle)) return (int) $status->id;
            }
        }

        return null;
    }

    private function workFormOptions(string $locale): array
    {
        $workTypeGroupId = $this->findGroupIdByKeywords(['type d oeuvre', 'type oeuvre']);
        $workCategoryGroupId = $this->findGroupIdByKeywords(['categorie pour oeuvre', 'categorie oeuvre']);
        $workStatusGroupId = $this->statusGroupIdForPage('work');
        $declassifiedStatusId = $this->findStatusIdByNameNeedles(['declassee', 'declass'], $workStatusGroupId);
        if (empty($declassifiedStatusId) && !empty($workStatusGroupId)) {
            $declassifiedStatusId = Status::where('group_id', $workStatusGroupId)->orderBy('id')->value('id');
        }

        $currencies = Currency::query()->orderBy('id')->get()->map(function ($c) use ($locale) {
            $name = $this->t($c, 'currency_name', $locale);
            $acronym = trim((string) ($c->currency_acronym ?? ''));
            return ['id' => (int) $c->id, 'label' => $acronym !== '' ? ($name . ' (' . $acronym . ')') : $name];
        })->values()->toArray();

        $typesQuery = Type::query()->orderBy('id');
        if (!empty($workTypeGroupId)) $typesQuery->where('group_id', $workTypeGroupId);
        $types = $typesQuery->get()->map(fn ($t) => ['id' => (int) $t->id, 'label' => $this->t($t, 'type_name', $locale)])->values()->toArray();

        $categoriesQuery = Category::query()->orderBy('id');
        if (!empty($workCategoryGroupId)) $categoriesQuery->where('group_id', $workCategoryGroupId);
        $categories = $categoriesQuery->get()->map(fn ($c) => ['id' => (int) $c->id, 'label' => $this->t($c, 'category_name', $locale)])->values()->toArray();

        $publishers = User::query()
            ->whereHas('roles', function (Builder $q) {
                $q->whereRaw('LOWER(role_name) LIKE ?', ['%publisher%'])
                    ->orWhereRaw('LOWER(role_name) LIKE ?', ['%publieur%']);
            })
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(fn ($u) => ['id' => (int) $u->id, 'label' => $this->userLabel($u)])
            ->values()
            ->toArray();

        $organizations = Organization::query()
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(function ($o) {
                $name = trim((string) ($o->org_name ?? ''));
                return ['id' => (int) $o->id, 'label' => $name !== '' ? $name : ('Organisation #' . $o->id)];
            })
            ->values()
            ->toArray();

        return [
            'currencies' => $currencies,
            'types' => $types,
            'categories' => $categories,
            'publishers' => $publishers,
            'organizations' => $organizations,
            'declassified_status_id' => $declassifiedStatusId,
        ];
    }

    private function normalizeStatusColor(?string $color): string
    {
        $value = Str::of((string) $color)
            ->lower()
            ->trim()
            ->replace('_', '-')
            ->replace(' ', '-')
            ->value();

        $aliases = [
            'grey' => 'secondary',
            'gray' => 'secondary',
            'red' => 'danger',
            'green' => 'success',
            'blue' => 'primary',
            'yellow' => 'warning',
            'orange' => 'warning',
            'teal' => 'info',
            'cyan' => 'info',
            'black' => 'dark',
            'white' => 'light',
        ];
        if (isset($aliases[$value])) {
            return $aliases[$value];
        }

        $allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
        if (in_array($value, $allowed, true)) {
            return $value;
        }

        return 'secondary';
    }

    public function addCountry(Request $request) {
        $v = Validator::make($request->all(), ['country_name' => 'required|string|max:255', 'country_phone_code' => 'nullable|string|max:45', 'country_lang_code' => 'nullable|string|max:45']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $country = Country::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_country_success'), ['id' => $country->id]);
    }
    public function updateCountry(Request $request, $id) {
        $v = Validator::make($request->all(), ['country_name' => 'required|string|max:255', 'country_phone_code' => 'nullable|string|max:45', 'country_lang_code' => 'nullable|string|max:45']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $country = Country::findOrFail($id);
        $country->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $country->id]);
    }
    public function addCurrency(Request $request) {
        $v = Validator::make($request->all(), ['currency_name' => 'required|string|max:255', 'currency_acronym' => 'required|string|max:45', 'currency_icon' => 'nullable|string|max:255']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $data = $v->validated(); $data['currency_name'] = ['fr' => $data['currency_name'], 'en' => $data['currency_name']];
        $currency = Currency::create($data);
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_currency_success'), ['id' => $currency->id]);
    }
    public function updateCurrency(Request $request, $id) {
        $v = Validator::make($request->all(), ['currency_name' => 'required|string|max:255', 'currency_acronym' => 'required|string|max:45', 'currency_icon' => 'nullable|string|max:255']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $data = $v->validated(); $data['currency_name'] = ['fr' => $data['currency_name'], 'en' => $data['currency_name']];
        $currency = Currency::findOrFail($id);
        $currency->update($data);
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $currency->id]);
    }
    public function addCurrencyEntity(Request $request, $entity) {
        if ($entity !== 'rate') return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'));
        $v = Validator::make($request->all(), ['from_currency_id' => 'required|integer|exists:currencies,id', 'to_currency_id' => 'required|integer|exists:currencies,id|different:from_currency_id', 'rate' => 'required|numeric|min:0.00001']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $rate = CurrenciesRate::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_currencies_rate_success'), ['id' => $rate->id]);
    }
    public function updateCurrencyEntity(Request $request, $entity, $id) {
        if ($entity !== 'rate') return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'));
        $v = Validator::make($request->all(), ['from_currency_id' => 'required|integer|exists:currencies,id', 'to_currency_id' => 'required|integer|exists:currencies,id|different:from_currency_id', 'rate' => 'required|numeric|min:0.00001']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $rate = CurrenciesRate::findOrFail($id);
        $rate->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $rate->id]);
    }
    public function addRole(Request $request) {
        $v = Validator::make($request->all(), ['role_name' => 'required|string|max:255', 'task' => 'nullable|string|max:255', 'role_description' => 'nullable|string']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $role = Role::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_role_success'), ['id' => $role->id]);
    }
    public function updateRole(Request $request, $id) {
        $v = Validator::make($request->all(), ['role_name' => 'required|string|max:255', 'task' => 'nullable|string|max:255', 'role_description' => 'nullable|string']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $role = Role::findOrFail($id);
        $role->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $role->id]);
    }
    public function addRoleEntity(Request $request, $entity) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateRoleEntity(Request $request, $entity, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addUsersEntity(Request $request, $entity)
    {
        if (!in_array($entity, ['partner', 'sponsor'], true)) {
            return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'));
        }

        $rules = [
            'name' => 'required|string|max:65535',
            'message' => 'nullable|string',
            'from_user_id' => 'required|integer|exists:users,id',
            'from_organization_id' => 'required|integer|exists:organizations,id',
            'image_url' => 'nullable|string|max:65535',
            'website_url' => 'nullable|string|max:65535',
        ];

        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());

        $data = $v->validated();
        $partner = Partner::create($data);

        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $partner->id]);
    }
    public function addGroup(Request $request) {
        $v = Validator::make($request->all(), ['group_name' => 'required|string|max:255', 'group_description' => 'nullable|string']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $group = Group::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_group_success'), ['id' => $group->id]);
    }
    public function updateGroup(Request $request, $id) {
        $v = Validator::make($request->all(), ['group_name' => 'required|string|max:255', 'group_description' => 'nullable|string']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $group = Group::findOrFail($id);
        $group->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $group->id]);
    }
    public function addGroupEntity(Request $request, $entity) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateGroupEntity(Request $request, $entity, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addReportReason(Request $request) {
        $v = Validator::make($request->all(), ['reason_content' => 'required|string|max:255', 'reports_count' => 'required|integer|min:1', 'blocked_for' => 'required|integer|min:1', 'entity' => 'nullable|in:work,user,message']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $data = $v->validated(); $data['reason_content'] = ['fr' => $data['reason_content'], 'en' => $data['reason_content']];
        $reason = ReportReason::create($data);
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_report_reason_success'), ['id' => $reason->id]);
    }
    public function updateReportReason(Request $request, $id) {
        $v = Validator::make($request->all(), ['reason_content' => 'required|string|max:255', 'reports_count' => 'required|integer|min:1', 'blocked_for' => 'required|integer|min:1', 'entity' => 'nullable|in:work,user,message']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $data = $v->validated(); $data['reason_content'] = ['fr' => $data['reason_content'], 'en' => $data['reason_content']];
        $reason = ReportReason::findOrFail($id);
        $reason->update($data);
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $reason->id]);
    }
    public function addReportReasonEntity(Request $request, $entity) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function updateReportReasonEntity(Request $request, $entity, $id) { return Redirect::back()->with('success_message', 'Operation enregistree.'); }
    public function addSubscription(Request $request) {
        $v = Validator::make($request->all(), ['number_of_hours' => 'nullable|integer|min:0', 'price' => 'nullable|numeric|min:0', 'currency_id' => 'nullable|integer|exists:currencies,id', 'type_id' => 'nullable|integer|exists:types,id', 'category_id' => 'nullable|integer|exists:categories,id']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $subscription = Subscription::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_subscription_success'), ['id' => $subscription->id]);
    }
    public function updateSubscription(Request $request, $id) {
        $v = Validator::make($request->all(), ['number_of_hours' => 'nullable|integer|min:0', 'price' => 'nullable|numeric|min:0', 'currency_id' => 'nullable|integer|exists:currencies,id', 'type_id' => 'nullable|integer|exists:types,id', 'category_id' => 'nullable|integer|exists:categories,id']);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $subscription = Subscription::findOrFail($id);
        $subscription->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $subscription->id]);
    }
    public function addOrganization(Request $request) {
        $v = Validator::make($request->all(), [
            'org_name' => 'required|string|max:65535',
            'org_acronym' => 'nullable|string|max:255',
            'org_description' => 'nullable|string',
            'id_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'p_o_box' => 'nullable|string|max:45',
            'legal_status' => 'nullable|string|max:255',
            'year_of_creation' => 'nullable|string|max:45',
            'website_url' => 'nullable|string|max:65535',
            'cover_url' => 'nullable|string|max:65535',
            'type_id' => 'nullable|integer|exists:types,id',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $organization = Organization::create($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $organization->id]);
    }
    public function updateOrganization(Request $request, $id) {
        $v = Validator::make($request->all(), [
            'org_name' => 'required|string|max:65535',
            'org_acronym' => 'nullable|string|max:255',
            'org_description' => 'nullable|string',
            'id_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'p_o_box' => 'nullable|string|max:45',
            'legal_status' => 'nullable|string|max:255',
            'year_of_creation' => 'nullable|string|max:45',
            'website_url' => 'nullable|string|max:65535',
            'cover_url' => 'nullable|string|max:65535',
            'type_id' => 'nullable|integer|exists:types,id',
            'status_id' => 'nullable|integer|exists:statuses,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $organization = Organization::findOrFail($id);
        $organization->update($v->validated());
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $organization->id]);
    }
    public function updateWork(Request $request, $id) {
        $workTypeGroupId = $this->findGroupIdByKeywords(['type d oeuvre', 'type oeuvre']);
        $workCategoryGroupId = $this->findGroupIdByKeywords(['categorie pour oeuvre', 'categorie oeuvre']);
        $workStatusGroupId = $this->statusGroupIdForPage('work');
        $v = Validator::make($request->all(), [
            'work_title' => 'required|string|max:255',
            'work_content' => 'required|string',
            'work_url' => 'nullable|string|max:500',
            'media_length' => ['nullable', 'regex:/^\d{2}:\d{2}:\d{2}$/'],
            'author' => 'nullable|string|max:255',
            'editor' => 'nullable|string|max:255',
            'is_public' => 'required|in:0,1',
            'consultation_price' => 'required_if:is_public,0|nullable|numeric|min:0.01',
            'currency_id' => 'required_if:is_public,0|nullable|integer|exists:currencies,id',
            'type_id' => 'required|integer|exists:types,id',
            'categories_ids' => 'required|array|min:1',
            'categories_ids.*' => 'integer|exists:categories,id',
            'status_id' => 'required|integer|exists:statuses,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'organization_id' => 'nullable|integer|exists:organizations,id',
        ]);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());
        $data = $v->validated();
        $mediaLengthSeconds = null;
        if (!empty($data['media_length'])) {
            [$hh, $mm, $ss] = array_map('intval', explode(':', $data['media_length']));
            if ($mm > 59 || $ss > 59) return $this->ajaxValidationOrRedirect($request, ['media_length' => ['Le format de la longueur doit etre HH:MM:SS valide.']]);
            $mediaLengthSeconds = ($hh * 3600) + ($mm * 60) + $ss;
        }
        if (!empty($workTypeGroupId) && !Type::whereKey($data['type_id'])->where('group_id', $workTypeGroupId)->exists()) return $this->ajaxValidationOrRedirect($request, ['type_id' => ['Type invalide pour les oeuvres.']]);
        if (!empty($workCategoryGroupId)) {
            $validCategoryCount = Category::whereIn('id', $data['categories_ids'])->where('group_id', $workCategoryGroupId)->count();
            if ($validCategoryCount !== count($data['categories_ids'])) return $this->ajaxValidationOrRedirect($request, ['categories_ids' => ['Categorie invalide pour les oeuvres.']]);
        }
        if (!empty($workStatusGroupId) && !Status::whereKey($data['status_id'])->where('group_id', $workStatusGroupId)->exists()) return $this->ajaxValidationOrRedirect($request, ['status_id' => ['Etat invalide pour les oeuvres.']]);
        $work = Work::findOrFail($id);
        $workUrl = trim((string) ($data['work_url'] ?? ''));
        $work->update([
            'work_title' => $data['work_title'],
            'work_content' => $data['work_content'],
            'work_url' => $workUrl !== '' ? $workUrl : null,
            'video_source' => $workUrl !== '' ? 'YouTube' : 'AWS',
            'media_length' => $mediaLengthSeconds,
            'author' => $data['author'] ?? null,
            'editor' => $data['editor'] ?? null,
            'is_public' => (int) $data['is_public'],
            'consultation_price' => ((int) $data['is_public'] === 0) ? ($data['consultation_price'] ?? null) : null,
            'currency_id' => ((int) $data['is_public'] === 0) ? ($data['currency_id'] ?? null) : null,
            'type_id' => $data['type_id'],
            'status_id' => $data['status_id'],
            'user_id' => $data['user_id'] ?? $work->user_id ?? auth()->id(),
            'organization_id' => $data['organization_id'] ?? null,
        ]);
        $work->categories()->sync($data['categories_ids']);
        return $this->ajaxOrRedirectSuccess($request, __('notifications.registered_data'), ['id' => $work->id]);
    }

    public function addWork(Request $request)
    {
        $workTypeGroupId = $this->findGroupIdByKeywords(['type d oeuvre', 'type oeuvre']);
        $workCategoryGroupId = $this->findGroupIdByKeywords(['categorie pour oeuvre', 'categorie oeuvre']);
        $workStatusGroupId = $this->statusGroupIdForPage('work');

        $v = Validator::make($request->all(), [
            'work_title' => 'required|string|max:255',
            'work_content' => 'required|string',
            'work_url' => 'nullable|string|max:500',
            'media_length' => ['nullable', 'regex:/^\d{2}:\d{2}:\d{2}$/' ],
            'author' => 'nullable|string|max:255',
            'editor' => 'nullable|string|max:255',
            'is_public' => 'required|in:0,1',
            'consultation_price' => 'required_if:is_public,0|nullable|numeric|min:0.01',
            'currency_id' => 'required_if:is_public,0|nullable|integer|exists:currencies,id',
            'type_id' => 'required|integer|exists:types,id',
            'categories_ids' => 'required|array|min:1',
            'categories_ids.*' => 'integer|exists:categories,id',
            'status_id' => 'required|integer|exists:statuses,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'files_urls.*' => 'file|max:512000',
        ]);
        if ($v->fails()) return $this->ajaxValidationOrRedirect($request, $v->errors()->toArray());

        $data = $v->validated();
        $mediaLengthSeconds = null;
        if (!empty($data['media_length'])) {
            [$hh, $mm, $ss] = array_map('intval', explode(':', $data['media_length']));
            if ($mm > 59 || $ss > 59) {
                return $this->ajaxValidationOrRedirect($request, ['media_length' => ['Le format de la longueur doit etre HH:MM:SS valide.']]);
            }
            $mediaLengthSeconds = ($hh * 3600) + ($mm * 60) + $ss;
        }

        if (!empty($workTypeGroupId) && !Type::whereKey($data['type_id'])->where('group_id', $workTypeGroupId)->exists()) {
            return $this->ajaxValidationOrRedirect($request, ['type_id' => ['Type invalide pour les oeuvres.']]);
        }
        if (!empty($workCategoryGroupId)) {
            $validCategoryCount = Category::whereIn('id', $data['categories_ids'])->where('group_id', $workCategoryGroupId)->count();
            if ($validCategoryCount !== count($data['categories_ids'])) {
                return $this->ajaxValidationOrRedirect($request, ['categories_ids' => ['Categorie invalide pour les oeuvres.']]);
            }
        }
        if (!empty($workStatusGroupId) && !Status::whereKey($data['status_id'])->where('group_id', $workStatusGroupId)->exists()) {
            return $this->ajaxValidationOrRedirect($request, ['status_id' => ['Etat invalide pour les oeuvres.']]);
        }

        $group = Group::where('group_name', 'Type de fichier')->first();
        if (!$group) return $this->ajaxValidationOrRedirect($request, ['files_urls' => ['Configuration des types de fichier introuvable.']]);
        $fileTypes = Type::query()->where('group_id', $group->id)->get();
        $image = $fileTypes->first(fn ($t) => str_contains(Str::of($this->t($t, 'type_name', 'fr'))->ascii()->lower()->value(), 'image'));
        $doc = $fileTypes->first(fn ($t) => str_contains(Str::of($this->t($t, 'type_name', 'fr'))->ascii()->lower()->value(), 'document'));
        $audio = $fileTypes->first(fn ($t) => str_contains(Str::of($this->t($t, 'type_name', 'fr'))->ascii()->lower()->value(), 'audio'));

        $workUrl = trim((string) ($data['work_url'] ?? ''));
        $inputs = [
            'work_title' => $data['work_title'],
            'work_content' => $data['work_content'],
            'work_url' => $workUrl !== '' ? $workUrl : null,
            'video_source' => $workUrl !== '' ? 'YouTube' : 'AWS',
            'media_length' => $mediaLengthSeconds,
            'author' => $data['author'] ?? null,
            'editor' => $data['editor'] ?? null,
            'is_public' => (int) $data['is_public'],
            'consultation_price' => ((int) $data['is_public'] === 0) ? ($data['consultation_price'] ?? null) : null,
            'currency_id' => ((int) $data['is_public'] === 0) ? ($data['currency_id'] ?? null) : null,
            'type_id' => $data['type_id'],
            'status_id' => $data['status_id'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'organization_id' => $data['organization_id'] ?? null,
        ];

        $work = Work::create($inputs);
        $work->categories()->sync($data['categories_ids']);

        if ($request->hasFile('files_urls')) {
            $files = $request->file('files_urls', []); $names = $request->input('files_names', []);
            foreach ($files as $k => $f) {
                $ext = strtolower($f->getClientOriginalExtension()); $uri = ''; $typeId = null;
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'avi', 'mov', 'mkv', 'webm'])) { $uri = 'images/works'; $typeId = $image?->id; }
                elseif (in_array($ext, ['pdf', 'doc', 'docx', 'txt'])) { $uri = 'documents/works'; $typeId = $doc?->id; }
                elseif (in_array($ext, ['mp3', 'wav', 'flac'])) { $uri = 'audios/works'; $typeId = $audio?->id; }
                else { return $this->handleError(__('notifications.type_is_not_file')); }
                $clean = sanitizeFileName($f->getClientOriginalName()); $path = $uri . '/' . $work->id . '/' . $clean;
                try { $f->storeAs($uri . '/' . $work->id, $clean, 's3'); } catch (\Throwable $th) { return $this->handleError($th, __('notifications.create_work_file_500'), 500); }
                File::create(['file_name' => trim($names[$k] ?? $clean), 'file_url' => config('filesystems.disks.s3.url') . $path, 'type_id' => $typeId, 'work_id' => $work->id]);
            }
        }

        return $this->ajaxOrRedirectSuccess($request, __('notifications.create_work_success'), ['id' => $work->id]);
    }

    private function ajaxValidationOrRedirect(Request $request, array $errors)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }
        return Redirect::back()->withErrors($errors);
    }

    private function ajaxOrRedirectSuccess(Request $request, string $message, array $data = [])
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
        }
        return Redirect::back()->with('success_message', $message);
    }
}
