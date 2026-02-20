<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Boongo admin" />
    <title>{{ $title ?? 'Boongo Admin' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}" />
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('templates/admin/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('templates/admin/assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('templates/admin/assets/css/theme.min.css') }}" />
    <style>
        .admin-search-desktop { min-width: 240px; max-width: 860px; width: 100%; }
        .admin-search-mobile-trigger { display: none; }
        .admin-avatar-fallback { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background: #e9efff; color: #1d4ed8; }
        .admin-quick-card { min-height: 120px; }
        .admin-table td, .admin-table th { vertical-align: middle; }
        .nxl-content { padding: 20px 22px 24px !important; }
        .logo.logo-lg { max-height: 36px; width: auto; object-fit: contain; }
        .logo.logo-sm { max-height: 28px; width: auto; object-fit: contain; }
        .modal { z-index: 2005 !important; }
        .modal-backdrop { z-index: 2000 !important; }
        .swal2-container { z-index: 1080; }
        @media (min-width: 768px) {
            .admin-search-mobile-trigger { display: none !important; }
        }
        @media (max-width: 767.98px) {
            .admin-search-desktop { display: none !important; }
            .admin-search-mobile-trigger { display: inline-flex !important; }
            .nxl-content { padding: 14px 12px 18px !important; }
        }
    </style>
</head>
<body>
    @php
        $authUser = auth()->user();
        $userName = trim(($authUser->firstname ?? '').' '.($authUser->lastname ?? '')) ?: ($authUser->surname ?? ($authUser->username ?? ('User #'.$authUser->id)));
        $avatarValue = (string) ($authUser?->avatar_url ?? '');
        if ($avatarValue !== '') {
            if (\Illuminate\Support\Str::startsWith($avatarValue, ['http://', 'https://', '//', 'data:'])) {
                $avatarUrl = $avatarValue;
            } elseif (\Illuminate\Support\Str::startsWith($avatarValue, ['/storage/', 'storage/'])) {
                $avatarUrl = asset(ltrim($avatarValue, '/'));
            } else {
                $avatarUrl = asset('storage/' . ltrim($avatarValue, '/'));
            }
        } else {
            $avatarUrl = null;
        }
        $localeMap = ['en' => ['label' => 'English', 'flag' => 'us'], 'fr' => ['label' => 'Français', 'flag' => 'fr']];
        $currentLocale = app()->getLocale();
        $searchValue = request('q');
        $notifications = \App\Models\Notification::with(['from_user','type','like.for_work'])
            ->where('to_user_id', $authUser->id)
            ->whereHas('type', fn ($q) => $q->whereRaw("RIGHT(alias, 6) = '_notif'"))
            ->latest()
            ->limit(3)
            ->get();
        $readIds = \App\Models\ReadNotification::where('to_user_id', $authUser->id)
            ->whereNotNull('notification_id')
            ->pluck('notification_id')
            ->toArray();
        $unreadCount = $notifications->whereNotIn('id', $readIds)->count();
        $isAdmin = $authUser->hasRole('Administrateur');
        $isManager = $authUser->hasRole('Manager');
        $adminNav = [
            ['key' => 'dashboard', 'label' => __('messages.nav.dashboard'), 'route' => 'admin.home', 'icon' => 'feather-airplay'],
            ['key' => 'country', 'label' => __('messages.nav.country'), 'route' => 'admin.country.home', 'icon' => 'feather-globe'],
            ['key' => 'currency', 'label' => __('messages.nav.currency'), 'route' => 'admin.currency.home', 'icon' => 'feather-dollar-sign'],
            ['key' => 'currency_rate', 'label' => __('messages.nav.currency_rate'), 'route' => 'admin.currency.entity.home', 'params' => ['entity' => 'rate'], 'icon' => 'feather-repeat'],
            ['key' => 'role', 'label' => __('messages.nav.role'), 'route' => 'admin.role.home', 'icon' => 'feather-shield'],
            ['key' => 'group', 'label' => __('messages.nav.group'), 'route' => 'admin.group.home', 'icon' => 'feather-layers'],
            ['key' => 'group_category', 'label' => __('messages.nav.category'), 'route' => 'admin.group.entity.home', 'params' => ['entity' => 'category'], 'icon' => 'feather-grid'],
            ['key' => 'group_type', 'label' => __('messages.nav.type'), 'route' => 'admin.group.entity.home', 'params' => ['entity' => 'type'], 'icon' => 'feather-tag'],
            ['key' => 'group_state', 'label' => __('messages.nav.state'), 'route' => 'admin.group.entity.home', 'params' => ['entity' => 'state'], 'icon' => 'feather-flag'],
            ['key' => 'report_reason', 'label' => __('messages.nav.report_reason'), 'route' => 'admin.report_reason.home', 'icon' => 'feather-alert-circle'],
            ['key' => 'report_reason_reported', 'label' => __('messages.nav.reported_elements'), 'route' => 'admin.report_reason.entity.home', 'params' => ['entity' => 'reported'], 'icon' => 'feather-archive'],
            ['key' => 'subscription', 'label' => __('messages.nav.subscription'), 'route' => 'admin.subscription.home', 'icon' => 'feather-credit-card'],
            ['key' => 'work', 'label' => __('messages.nav.work'), 'route' => 'admin.work.home', 'icon' => 'feather-book-open'],
            ['key' => 'users', 'label' => __('messages.nav.users'), 'route' => 'admin.users.home', 'icon' => 'feather-users'],
            ['key' => 'users_partner', 'label' => __('messages.nav.partner'), 'route' => 'admin.users.entity.home', 'params' => ['entity' => 'partner'], 'icon' => 'feather-link-2'],
            ['key' => 'users_sponsor', 'label' => __('messages.nav.sponsor'), 'route' => 'admin.users.entity.home', 'params' => ['entity' => 'sponsor'], 'icon' => 'feather-award'],
            ['key' => 'notifications', 'label' => __('messages.nav.notifications'), 'route' => 'admin.notifications.home', 'icon' => 'feather-bell'],
        ];
        $managerNav = [
            ['key' => 'manager_dashboard', 'label' => __('messages.nav.manager_dashboard'), 'route' => 'manager.home', 'icon' => 'feather-pie-chart'],
            ['key' => 'manager_members', 'label' => __('messages.nav.manager_members'), 'route' => 'manager.members.home', 'icon' => 'feather-users'],
            ['key' => 'manager_establishments', 'label' => __('messages.nav.manager_establishments'), 'route' => 'manager.establishments.home', 'icon' => 'feather-home'],
            ['key' => 'manager_institutions', 'label' => __('messages.nav.manager_institutions'), 'route' => 'manager.institutions.home', 'icon' => 'feather-briefcase'],
            ['key' => 'manager_reported', 'label' => __('messages.nav.manager_reported'), 'route' => 'manager.reported.home', 'icon' => 'feather-flag'],
            ['key' => 'manager_notifications', 'label' => __('messages.nav.notifications'), 'route' => 'manager.notifications.home', 'icon' => 'feather-bell'],
        ];
        if ($isAdmin && $isManager) {
            $managerNav = array_values(array_filter($managerNav, fn ($item) => $item['key'] !== 'manager_members'));
        }
        $nav = [];
        if ($isAdmin) $nav = array_merge($nav, $adminNav);
        if ($isManager) $nav = array_merge($nav, $managerNav);
        $homeRoute = ($isManager && !$isAdmin) ? route('manager.home') : route('admin.home');
        $notificationsPageRoute = ($isManager && !$isAdmin) ? route('manager.notifications.home') : route('admin.notifications.home');
        $workDetailRouteName = ($isManager && !$isAdmin) ? 'manager.work.datas' : 'admin.work.datas';
    @endphp

    <nav class="nxl-navigation">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="{{ $homeRoute }}" class="b-brand">
                    <img src="{{ asset('assets/img/brand.png') }}" alt="logo" class="logo logo-lg" />
                    <img src="{{ asset('assets/img/logo.png') }}" alt="logo" class="logo logo-sm" />
                </a>
            </div>
            <div class="navbar-content">
                <ul class="nxl-navbar">
                    <li class="nxl-item nxl-caption"><label>Administration Boongo</label></li>
                    @foreach ($nav as $item)
                        @php $isActive = isset($pageKey) && $pageKey === $item['key']; $url = isset($item['params']) ? route($item['route'], $item['params']) : route($item['route']); @endphp
                        <li class="nxl-item {{ $isActive ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ $url }}">
                                <span class="nxl-micon"><i class="{{ $item['icon'] }}"></i></span>
                                <span class="nxl-mtext">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>

    <header class="nxl-header">
        <div class="header-wrapper">
            <div class="header-left d-flex align-items-center gap-3 flex-grow-1">
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn"><div class="hamburger-box"><div class="hamburger-inner"></div></div></div>
                </a>
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button"><i class="feather-align-left"></i></a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none"><i class="feather-arrow-right"></i></a>
                </div>
                <form class="admin-search-desktop d-flex align-items-center ms-2 position-relative" action="{{ url()->current() }}" method="GET">
                    <div class="input-group">
                        <span class="input-group-text"><i class="feather-search"></i></span>
                        <input type="text" name="q" id="desktopAdminSearch" class="form-control" value="{{ $searchValue }}" placeholder="{{ __('messages.search.placeholder') }}" autocomplete="off" />
                    </div>
                    <div id="desktopSearchSuggest" class="dropdown-menu nxl-h-dropdown w-100 mt-1" style="display:none; max-height: 360px; overflow:auto;"></div>
                </form>
            </div>

            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 admin-search-mobile-trigger" data-bs-toggle="collapse" data-bs-target="#mobileSearchBar">
                        <i class="feather-search"></i>
                    </a>

                    <div class="dropdown nxl-h-item nxl-header-language">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown">
                            <img src="{{ asset('templates/admin/assets/vendors/img/flags/4x3/'.($localeMap[$currentLocale]['flag'] ?? 'us').'.svg') }}" alt="" class="img-fluid wd-20" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown">
                            @foreach ($available_locales ?? ['English' => 'en', 'Français' => 'fr'] as $label => $code)
                                <a href="{{ route('change_language', ['locale' => $code]) }}" class="dropdown-item {{ $currentLocale === $code ? 'active' : '' }}">
                                    <span>{{ $label }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button" id="themeDarkBtn"><i class="feather-moon"></i></a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" id="themeLightBtn" style="display: none"><i class="feather-sun"></i></a>
                    </div>

                    <div class="dropdown nxl-h-item">
                        <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button">
                            <i class="feather-bell"></i>
                            @if ($unreadCount > 0)
                                <span class="badge bg-danger nxl-h-badge">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                            <div class="d-flex justify-content-between align-items-center notifications-head">
                                <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                            </div>
                            @forelse ($notifications as $n)
                                @php
                                    $fromName = trim(($n->from_user->firstname ?? '').' '.($n->from_user->lastname ?? '')) ?: ($n->from_user->username ?? 'Système');
                                    $notifAlias = $n->type?->alias;
                                    $typeName = ($notifAlias && \Illuminate\Support\Facades\Lang::has('notifications.'.$notifAlias))
                                        ? __('notifications.'.$notifAlias, ['from_user_names' => $fromName])
                                        : __('messages.notifications.default');
                                    $isRead = in_array($n->id, $readIds);
                                    $notifIcon = $n->type?->icon ?: 'feather-bell';
                                    $notifUrl = !empty($n->work_id)
                                        ? route($workDetailRouteName, ['id' => $n->work_id])
                                        : (($n->like && !empty($n->like->for_work_id))
                                            ? route($workDetailRouteName, ['id' => $n->like->for_work_id])
                                            : ((!empty($n->event_id) || !empty($n->circle_id))
                                                ? (($isManager && !$isAdmin)
                                                    ? route('manager.members.datas', ['id' => (int) $n->from_user_id])
                                                    : route('admin.users.entity.datas', ['entity' => 'member', 'id' => (int) $n->from_user_id]))
                                                : $notificationsPageRoute . '?focus=' . $n->id));
                                @endphp
                                <div class="notifications-item">
                                    <div class="me-2 rounded-circle bg-soft-primary text-primary d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                        <i class="{{ $notifIcon }}"></i>
                                    </div>
                                    <div class="notifications-desc w-100">
                                        <a href="{{ $notifUrl }}" class="font-body text-truncate-2-line">
                                            <span class="fw-semibold text-dark">{{ $fromName }}</span> {{ $typeName }}
                                        </a>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="notifications-date text-muted border-bottom border-bottom-dashed">{{ optional($n->created_at)->diffForHumans() }}</div>
                                            @if (!$isRead)
                                                <span class="badge bg-soft-danger text-danger">Nouveau</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-muted">Aucune notification.</div>
                            @endforelse
                            <div class="text-center notifications-footer">
                                <a href="{{ $notificationsPageRoute }}" class="fs-13 fw-semibold text-dark">Voir tout</a>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown nxl-h-item">
                        <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="user-image" class="img-fluid user-avtar me-0" />
                            @else
                                <span class="admin-avatar-fallback">{{ strtoupper(mb_substr($userName, 0, 1)) }}</span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex align-items-center">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="user-image" class="img-fluid user-avtar" />
                                    @else
                                        <span class="admin-avatar-fallback me-2">{{ strtoupper(mb_substr($userName, 0, 1)) }}</span>
                                    @endif
                                    <div>
                                        <h6 class="text-dark mb-0">{{ $userName }}</h6>
                                        <span class="fs-12 fw-medium text-muted">{{ $authUser->email }}</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('account') }}" class="dropdown-item">
                                <i class="feather-settings"></i>
                                <span>Paramètres</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="feather-log-out"></i>
                                    <span>Déconnexion</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="mobileSearchBar" class="collapse px-3 pb-3 position-relative">
            <form action="{{ url()->current() }}" method="GET" class="position-relative">
                <div class="input-group">
                    <span class="input-group-text"><i class="feather-search"></i></span>
                    <input type="text" name="q" id="mobileAdminSearch" class="form-control" value="{{ $searchValue }}" placeholder="{{ __('messages.search.placeholder') }}" autocomplete="off" />
                </div>
                <div id="mobileSearchSuggest" class="dropdown-menu nxl-h-dropdown w-100 mt-1" style="display:none; max-height: 360px; overflow:auto;"></div>
            </form>
        </div>
    </header>

    <main class="nxl-container">
        <div class="nxl-content">
            @if (session('success_message')) <div class="alert alert-success" role="alert">{{ session('success_message') }}</div> @endif
            @if (session('error_message')) <div class="alert alert-danger" role="alert">{{ session('error_message') }}</div> @endif
            @yield('content')
        </div>
        <footer class="footer">
            <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright"><span>Copyright &copy; {{ date('Y') }}</span></p>
            <div class="d-flex align-items-center gap-4">
                <a href="{{ $homeRoute }}" class="fs-11 fw-semibold text-uppercase">Dashboard</a>
                <a href="{{ route('account') }}" class="fs-11 fw-semibold text-uppercase">{{ __('messages.nav.account') }}</a>
            </div>
        </footer>
    </main>

    <script src="{{ asset('templates/admin/assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('templates/admin/assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/addons/cooladmin/chartjs/Chart.bundle.min.js') }}"></script>
    <script src="{{ asset('templates/admin/assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('templates/admin/assets/js/theme-customizer-init.min.js') }}"></script>
    <script>
        (function () {
            const buildSuggestionHtml = (payload) => {
                if (!payload || !payload.groups || payload.groups.length === 0) return '';
                return payload.groups.map((group) => {
                    const items = group.items.map((item) => `<a href="${item.url || '#'}" class="dropdown-item">${item.label}</a>`).join('');
                    return `<div class="px-3 py-2 border-bottom"><h6 class="mb-2 fs-12 text-muted">${group.label}</h6>${items}</div>`;
                }).join('');
            };
            const bindAutocomplete = (inputId, menuId) => {
                const input = document.getElementById(inputId);
                const menu = document.getElementById(menuId);
                if (!input || !menu) return;
                let timer = null;
                input.addEventListener('input', function () {
                    const term = input.value.trim();
                    clearTimeout(timer);
                    if (term.length === 0) { menu.style.display = 'none'; menu.innerHTML = ''; return; }
                    timer = setTimeout(async () => {
                        try {
                            const resp = await fetch(`{{ route('app.search.suggest') }}?term=${encodeURIComponent(term)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                            const data = await resp.json();
                            const html = buildSuggestionHtml(data);
                            if (html === '') { menu.style.display = 'none'; menu.innerHTML = ''; return; }
                            menu.innerHTML = html;
                            menu.style.display = 'block';
                        } catch (e) { menu.style.display = 'none'; }
                    }, 180);
                });
                document.addEventListener('click', function (e) {
                    if (!menu.contains(e.target) && e.target !== input) menu.style.display = 'none';
                });
            };
            bindAutocomplete('desktopAdminSearch', 'desktopSearchSuggest');
            bindAutocomplete('mobileAdminSearch', 'mobileSearchSuggest');
        })();
    </script>
</body>
</html>
