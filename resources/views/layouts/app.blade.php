<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="bng-url" content="{{ getWebURL() }}">
        <meta name="bng-api-url" content="{{ getApiURL() }}">
        <meta name="bng-visitor" content="{{ !empty(Auth::user()) ? Auth::user()->id : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bng-ref" content="{{ !empty(Auth::user()) ? Auth::user()->api_token : null }}">

        <!-- ============ Favicon ============ -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- ============ Bootstrap icons ============ -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

        <!-- ============ Stylesheet ============ -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/duralux/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('templates/admin/assets/vendors/css/vendors.min.css') }}">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link rel="stylesheet" type="text/css" href="">
        <link rel="stylesheet" href="{{ asset('templates/admin/assets/css/theme.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}" />

        <!--! HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries !-->
        <!--! WARNING: Respond.js doesn"t work if you view the page via file: !-->
        <!--[if lt IE 9]>
			<script src="https:oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https:oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

        <title>
@if (!empty($page_title))
			{{ $page_title }}
@else
			Administration
@endif
		</title>
    </head>

    <body>
        <!--! ================================================================ !-->
        <!--! [End]  Modals !-->
        <!--! ================================================================ !-->
        <!--! ================================================================ !-->
        <!--! [Start] Navigation Manu !-->
        <!--! ================================================================ !-->
        <nav class="nxl-navigation">
            <div class="navbar-wrapper">
                <div class="m-header">
                    <a href="{{ route('admin.home') }}" class="b-brand">
                        <!-- ========   change your logo hear   ============ -->
                        <img src="{{ asset('assets/img/brand.png') }}" alt="" class="logo logo-lg" width="160" />
                        <img src="{{ asset('assets/img/logo.png') }}" alt="" class="logo logo-sm" width="51" />
                    </a>
                </div>
                <div class="navbar-content">
                    <ul class="nxl-navbar">
                        <li class="nxl-item nxl-caption">
                            <label>Navigation</label>
                        </li>
                        <!-- Dashboard -->
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.home') }}">
                                <span class="nxl-micon"><i class="feather-airplay"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.dashboard')</span>
                            </a>
                        </li>
                        <!-- Countries -->
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.country.home') }}">
                                <span class="nxl-micon"><i class="feather-flag"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.country')</span>
                            </a>
                        </li>
                        <!-- Currencies -->
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.currency.home') }}">
                                <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.currency.title')</span>
                            </a>
                        </li>
                        <!-- Roles -->
                        <li class="nxl-item nxl-hasmenu">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-users"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.role.title')</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.role.home') }}">@lang('miscellaneous.admin.role.link')</a></li>
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.role.entity.home', ['entity' => 'users']) }}">@lang('miscellaneous.menu.admin.role.users')</a></li>
                            </ul>
                        </li>
                        <!-- Groups -->
                        <li class="nxl-item nxl-hasmenu">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-anchor"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.group.title')</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.group.home') }}">@lang('miscellaneous.admin.group.link')</a></li>
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.group.entity.home', ['entity' => 'category']) }}">@lang('miscellaneous.menu.admin.group.category')</a></li>
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.group.entity.home', ['entity' => 'type']) }}">@lang('miscellaneous.menu.admin.group.type')</a></li>
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.group.entity.home', ['entity' => 'status']) }}">@lang('miscellaneous.menu.admin.group.status')</a></li>
                            </ul>
                        </li>
                        <!-- Reported reason -->
                        <li class="nxl-item nxl-hasmenu">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-shield-off"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.report-reason.title')</span><span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.report_reason.home') }}">@lang('miscellaneous.menu.admin.report-reason.link')</a></li>
                                <li class="nxl-item"><a class="nxl-link" href="{{ route('admin.report_reason.entity.home', ['entity' => 'reported']) }}">@lang('miscellaneous.menu.admin.report-reason.reported')</a></li>
                            </ul>
                        </li>
                        <!-- Subscriptions -->
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.subscription.home') }}">
                                <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.subscription')</span>
                            </a>
                        </li>
                        <!-- Works -->
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('admin.work.home') }}">
                                <span class="nxl-micon"><i class="feather-edit"></i></span>
                                <span class="nxl-mtext">@lang('miscellaneous.menu.admin.work')</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!--! ================================================================ !-->
        <!--! [End]  Navigation Manu !-->
        <!--! ================================================================ !-->
        <!--! ================================================================ !-->
        <!--! [Start] Alert !-->
        <!--! ================================================================ !-->
        <div id="ajax-alert-container"></div>
@if (\Session::has('success_message'))
        <div class="row position-fixed w-100" style="opacity: 0.9; z-index: 99999;">
            <div class="col-lg-4 col-sm-6 mx-auto">
                <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                    <i class="bi bi-info-circle me-2 fs-4" style="vertical-align: -3px;"></i> {!! \Session::get('success_message') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            </div>
        </div>
@endif
@if (\Session::has('error_message'))
        <div class="row position-fixed w-100" style="opacity: 0.9; z-index: 99999;">
            <div class="col-lg-4 col-sm-6 mx-auto">
                <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                    <i class="bi bi-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i> {!! \Session::get('error_message') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            </div>
        </div>
@endif
        <!--! ================================================================ !-->
        <!--! [End]  Alert !-->
        <!--! ================================================================ !-->
        <!--! ================================================================ !-->
        <!--! [Start] Header !-->
        <!--! ================================================================ !-->
        <header class="nxl-header">
            <div class="header-wrapper">
                <!--! [Start] Header Left !-->
                <div class="header-left d-flex align-items-center gap-4">
                    <!--! [Start] nxl-head-mobile-toggler !-->
                    <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                    <!--! [Start] nxl-head-mobile-toggler !-->
                    <!--! [Start] nxl-navigation-toggle !-->
                    <div class="nxl-navigation-toggle">
                        <a href="javascript:void(0);" id="menu-mini-button">
                            <i class="feather-align-left"></i>
                        </a>
                        <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                            <i class="feather-arrow-right"></i>
                        </a>
                    </div>
                    <!--! [End] nxl-navigation-toggle !-->

                    <!--! [Start] nxl-search !-->
                    <div class="dropdown nxl-h-item nxl-header-search">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="feather-search"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-start nxl-h-dropdown nxl-search-dropdown">
                            <div class="input-group search-form">
                                <span class="input-group-text">
                                    <i class="feather-search fs-6 text-muted"></i>
                                </span>
                                <input type="text" class="form-control search-input-field" placeholder="@lang('miscellaneous.search_input')" />
                                <span class="input-group-text">
                                    <button type="button" class="btn-close"></button>
                                </span>
                            </div>
                            <div class="dropdown-divider mt-0"></div>
                            <div class="search-items-wrapper">
                                <div class="searching-for px-4 py-2">
                                    <p class="fs-11 fw-medium text-muted">@lang('miscellaneous.search_info')</p>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('project_sectors')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('projects')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('categories')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('products')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('services')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('membres')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('titles')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('admins')</a>
                                        <a href="javascript:void(0);" class="flex-fill border rounded py-1 px-2 text-center fs-11 fw-semibold">@lang('complaints')</a>
                                    </div>
                                </div>
                                <div class="dropdown-divider my-3"></div>
                                <div class="users-result px-4 py-2">
                                    <h4 class="fs-13 fw-normal text-gray-600 mb-3">@lang('search_members') <span class="badge small bg-gray-200 rounded ms-1 text-dark">2356</span></h4>
{{-- @forelse ($members as $user)
    @if ($loop->index < 3)
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-image rounded">
                                                <img src="{{ $user['avatar_url'] }}" alt="{{ $user['firstname'] . ' ' . $user['lastname'] }}" class="img-fluid" />
                                            </div>
                                            <div>
                                                <a href="{{ route('dashboard.role.entity.datas', ['entity' => 'members', 'id' => $user['id']]) }}" class="font-body fw-bold d-block mb-1">{{ $user['firstname'] . ' ' . $user['lastname'] }}</a>
                                                <p class="fs-11 text-muted mb-0">{{ !empty($user['email']) ? $user['email'] : $user['phone'] }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('dashboard.role.entity.datas', ['entity' => 'members', 'id' => $user['id']]) }}" class="avatar-text avatar-md">
                                            <i class="feather-chevron-right"></i>
                                        </a>
                                    </div>
    @endif
@empty
@endforelse --}}
                                </div>

                                <div class="dropdown-divider my-3"></div>
                                <div class="file-result px-4 py-2">
                                    <h4 class="fs-13 fw-normal text-gray-600 mb-3">@lang('search_sectors') <span class="badge small bg-gray-200 rounded ms-1 text-dark">908</span></h4>
{{-- @forelse ($sectors as $sector)
    @if ($loop->index < 3)
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div>
                                                <a href="{{ route('dashboard.sector.datas', ['id' => $sector['id']]) }}" class="font-body fw-bold d-block mb-1">{{ $sector['sector_name'] }}</a>
                                            </div>
                                        </div>
                                        <a href="{{ route('dashboard.sector.datas', ['id' => $sector['id']]) }}" class="avatar-text avatar-md">
                                            <i class="feather-chevron-right"></i>
                                        </a>
                                    </div>
    @endif
@empty
@endforelse --}}
                                </div>

                                <div class="dropdown-divider my-3"></div>
                                <div class="file-result px-4 py-2">
                                    <h4 class="fs-13 fw-normal text-gray-600 mb-3">@lang('search_categories') <span class="badge small bg-gray-200 rounded ms-1 text-dark">7852</span></h4>
{{-- @forelse ($m_categories as $category)
    @if ($loop->index < 3)
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div>
                                                <a href="{{ route('dashboard.category.datas', ['id' => $category['id']]) }}" class="font-body fw-bold d-block mb-1">{{ $category['category_name'] }}</a>
                                            </div>
                                        </div>
                                        <a href="{{ route('dashboard.category.datas', ['id' => $category['id']]) }}" class="avatar-text avatar-md">
                                            <i class="feather-chevron-right"></i>
                                        </a>
                                    </div>
    @endif
@empty
@endforelse --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--! [End] nxl-search !-->

                    <!--! [Start] ajax-loader !-->
                    <div id="ajax-loader" class="d-none">
                        <img src="{{ asset('assets/img/ajax-loading.gif') }}" alt="@lang('miscellaneous.loading')" width="32" height="32">
                    </div>
                    <!--! [End] ajax-loader !-->
                </div>
                <!--! [End] Header Left !-->
                <!--! [Start] Header Right !-->
                <div class="header-right ms-auto">
                    <div class="d-flex align-items-center">
                        <div class="dropdown nxl-h-item nxl-header-language d-none d-sm-flex">
                            <a href="javascript:void(0);" class="nxl-head-link me-0 nxl-language-link" data-bs-toggle="dropdown" data-bs-auto-close="outside">
@if (app()->getLocale() == 'en')
                                <img src="{{ asset('templates/admin/assets/vendors/img/flags/4x3/us.svg') }}" alt="" class="img-fluid wd-20" />
@else
                                <img src="{{ asset('templates/admin/assets/vendors/img/flags/4x3/fr.svg') }}" alt="" class="img-fluid wd-20" />
@endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-language-dropdown">
                                <div class="dropdown-divider mt-0"></div>
                                <div class="language-items-wrapper">
                                    <div class="select-language px-4 py-2 hstack justify-content-between gap-4">
                                        <div class="lh-lg">
                                            <h6 class="mb-0">@lang('miscellaneous.your_language')</h6>
                                            {{-- <p class="fs-11 text-muted mb-0">2 languages avaiable!</p> --}}
                                        </div>
                                        {{-- <a href="javascript:void(0);" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Add Language">
                                            <i class="feather-plus"></i>
                                        </a> --}}
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <div class="row px-4 pt-3">
                                        <div class="col-sm-4 col-6 language_select">
                                            <a href="{{ route('change_language', ['locale' => 'en']) }}" class="d-flex align-items-center gap-2">
                                                <div class="avatar-image avatar-sm"><img src="{{ asset('templates/admin/assets/vendors/img/flags/4x3/us.svg') }}" alt="" class="img-fluid" /></div>
                                                <span>English</span>
                                            </a>
                                        </div>
                                        <div class="col-sm-4 col-6 language_select">
                                            <a href="{{ route('change_language', ['locale' => 'fr']) }}" class="d-flex align-items-center gap-2">
                                                <div class="avatar-image avatar-sm"><img src="{{ asset('templates/admin/assets/vendors/img/flags/4x3/fr.svg') }}" alt="" class="img-fluid" /></div>
                                                <span>Français</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nxl-h-item d-none d-sm-flex">
                            <div class="full-screen-switcher">
                                <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                    <i class="feather-maximize maximize"></i>
                                    <i class="feather-minimize minimize"></i>
                                </a>
                            </div>
                        </div>
                        <div class="nxl-h-item dark-light-theme">
                            <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                                <i class="feather-moon"></i>
                            </a>
                            <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                                <i class="feather-sun"></i>
                            </a>
                        </div>
                        <div class="dropdown nxl-h-item">
                            <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                                <i class="feather-bell"></i>
                                <span class="badge bg-danger nxl-h-badge"></span>
                            </a>
                            {{-- <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                                <div class="d-flex justify-content-between align-items-center notifications-head">
                                    <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                                    <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip" title="Make as Read">
                                        <i class="feather-check"></i>
                                        <span>Make as Read</span>
                                    </a>
                                </div>
                                <div class="notifications-item">
                                    <img src="/assets/images/avatar/4.png" alt="" class="rounded me-3 border" />
                                    <div class="notifications-desc">
                                        <a href="javascript:void(0);" class="font-body text-truncate-2-line"> <span class="fw-semibold text-dark">Archie Cantones</span> Don't forget to pickup Jeremy after school!</a>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="notifications-date text-muted border-bottom border-bottom-dashed">53 minutes ago</div>
                                            <div class="d-flex align-items-center float-end gap-2">
                                                <a href="javascript:void(0);" class="d-block wd-8 ht-8 rounded-circle bg-gray-300" data-bs-toggle="tooltip" title="Make as Read"></a>
                                                <a href="javascript:void(0);" class="text-danger" data-bs-toggle="tooltip" title="Remove">
                                                    <i class="feather-x fs-12"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center notifications-footer">
                                    <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">Alls Notifications</a>
                                </div>
                            </div> --}}
                        </div>
                        <div class="dropdown nxl-h-item">
                            <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                                <img src="{{ $current_user['avatar_url'] ?? asset('assets/img/user.png') }}" alt="user-image" class="img-fluid user-avtar me-0" />
                            </a>
                            <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                                <div class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $current_user['avatar_url'] }}" alt="user-image" class="img-fluid user-avtar" />
                                        <div>
                                            <h6 class="text-dark mb-0">{{ $current_user['firstname'] . ' ' . $current_user['lastname']  }} <span class="badge bg-soft-success text-success ms-1">ADMIN</span></h6>
                                            <span class="fs-12 fw-medium text-muted">{{ !empty($current_user['email']) ? $current_user['email'] : $current_user['phone'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('account') }}" class="dropdown-item">
                                    <i class="feather-settings"></i>
                                    <span>@lang('miscellaneous.menu.account.title')</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('logout') }}" method="POST">
@csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="feather-log-out"></i>
                                        <span>@lang('miscellaneous.logout')</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!--! [End] Header Right !-->
            </div>
        </header>
        <!--! ================================================================ !-->
        <!--! [End] Header !-->
        <!--! ================================================================ !-->
        <!--! ================================================================ !-->
        <!--! [Start] Main Content !-->
        <!--! ================================================================ !-->
        <main class="nxl-container">
@yield('app-content')
            <!-- [ Footer ] start -->
            <footer class="footer">
                <p class="fs-11 text-muted fw-medium text-uppercase mb-0 copyright">
                    <span>Copyright ©</span>
                    <script>
                        document.write(new Date().getFullYear());
                    </script>
                    <a href="https://start-africa.com" target="_blank">START</a>
                </p>
                <div class="d-flex align-items-center gap-1">
                    <span>Designed by</span> <a href="https://xsamtech.com" target="_blank" class="fs-11 fw-semibold text-uppercase">Xsam Technologies</a>
                </div>
            </footer>
            <!-- [ Footer ] end -->
        </main>
        <!--! ================================================================ !-->
        <!--! [End] Main Content !-->
        <!--! ================================================================ !-->
        <!--! ================================================================ !-->
        <!--! Footer Script !-->
        <!--! ================================================================ !-->
        <!-- jQuery JS -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <!-- Bootstrap core JS -->
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> --}}
        <!-- Vendors JS -->
        <script src="{{ asset('templates/admin/assets/vendors/js/vendors.min.js') }}"></script>
@if (Route::is('admin.home'))
        <!-- Apex Chart JS -->
        <script src="{{ asset('templates/admin/assets/vendors/js/apexcharts.min.js') }}"></script>
@endif
        <!-- Circle progress JS -->
        <script src="{{ asset('templates/admin/assets/vendors/js/circle-progress.min.js') }}"></script>
        <!--Apps Init -->
        <script src="{{ asset('templates/admin/assets/js/common-init.min.js') }}"></script>
@if (Route::is('admin.home'))
        <script src="{{ asset('templates/admin/assets/js/dashboard-init.min.js') }}"></script>
@endif
        <!-- Theme Customizer -->
        <script src="{{ asset('templates/admin/assets/js/theme-customizer-init.min.js') }}"></script>
        <!--! END: Theme Customizer !-->
        <!--! BEGIN: Custom JS  !-->
        <script type="text/javascript" src="{{ asset('assets/addons/custom/flatpickr/dist/flatpickr.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('assets/addons/custom/flatpickr/dist/fr.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <!-- Autosize JS-->
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <!-- Cropper JS-->
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <!-- Sweet Alert JS-->
        <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('assets/js/script.custom.js') }}"></script>
        <script type="text/javascript">
            /**
             * Change status
             */
            function changeIs(entity, element) {
                var _this = document.getElementById(element.id);
                var entity_id = parseInt(_this.id.split('-')[1]);
                var isDelivered = parseInt(_this.getAttribute('data-is-delivered'));

                Swal.fire({
                    title: '{{ __("miscellaneous.alert.attention.presence_payment") }}',
                    text: '{{ __("miscellaneous.alert.confirm.presence_payment") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#04471a',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("miscellaneous.alert.yes.presence_payment") }}',
                    cancelButtonText: '{{ __("miscellaneous.cancel") }}'

                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: headers,
                            type: "POST",
                            url: `${currentHost}/products/${entity}/${entity_id}`,
                            contentType: false,
                            processData: false,
                            data: JSON.stringify({ "entity" : entity, "id" : entityId, "is_delivered" : (isDelivered === 0 ? 1 : 0) }),
                            success: function (result) {
                                if (!result.success) {
                                    Swal.fire({
                                        title: '{{ __("miscellaneous.alert.oups") }}',
                                        text: result.message,
                                        icon: 'error'
                                    });

                                } else {
                                    Swal.fire({
                                        title: '{{ __("miscellaneous.alert.perfect") }}',
                                        text: result.message,
                                        icon: 'success'
                                    });
                                    location.reload();
                                }
                            },
                            error: function (xhr, error, status_description) {
                                console.log(xhr.responseJSON);
                                console.log(xhr.status);
                                console.log(error);
                                console.log(status_description);
                            }
                        });

                    } else {
                        Swal.fire({
                            title: '{{ __("miscellaneous.cancel") }}',
                            text: '{{ __("miscellaneous.alert.canceled.presence_payment") }}',
                            icon: 'error'
                        });
                    }
                });
            }

            /**
             * Perform action on element
             */
            function performAction(action, entity, entity_id) {
                if (action === 'delete') {
                    var entityId = parseInt(entity_id.split('-')[1]);

                    Swal.fire({
                        title: '{{ __("miscellaneous.alert.attention.delete") }}',
                        text: '{{ __("miscellaneous.alert.confirm.delete") }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#04471a',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '{{ __("miscellaneous.alert.yes.delete") }}',
                        cancelButtonText: '{{ __("miscellaneous.cancel") }}'

                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                headers: headers,
                                type: 'DELETE',
                                url: `${currentHost}/delete/${entity}/${entityId}`,
                                contentType: false,
                                processData: false,
                                data: JSON.stringify({ "entity" : entity, "id" : entityId }),
                                success: function (result) {
                                    if (!result.success) {
                                        Swal.fire({
                                            title: '{{ __("miscellaneous.alert.oups") }}',
                                            text: result.message,
                                            icon: 'error'
                                        });

                                    } else {
                                        Swal.fire({
                                            title: '{{ __("miscellaneous.alert.perfect") }}',
                                            text: result.message,
                                            icon: 'success'
                                        });
                                        location.reload();
                                    }
                                },
                                error: function (xhr, error, status_description) {
                                    console.log(xhr.responseJSON);
                                    console.log(xhr.status);
                                    console.log(error);
                                    console.log(status_description);
                                }
                            });

                        } else {
                            Swal.fire({
                                title: '{{ __("miscellaneous.cancel") }}',
                                text: '{{ __("miscellaneous.alert.canceled.delete") }}',
                                icon: 'error'
                            });
                        }
                    });
                }
            }

            /**
             * Show alert on Ajax
             * 
             * @param string current
             * @param string element
             */
            function showAjaxAlert(type, message) {
                const icon = type === 'success'
                    ? '<i class="bi bi-info-circle me-2 fs-4" style="vertical-align: -3px;"></i>'
                    : '<i class="bi bi-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i>';

                const alertHtml = `
                    <div class="position-relative">
                        <div class="row position-fixed w-100" style="opacity: 0.9; z-index: 999;">
                            <div class="col-lg-4 col-sm-6 mx-auto">
                                <div class="alert alert-${type} alert-dismissible fade show rounded-0 cnpr-line-height-1_1" role="alert">
                                    ${icon} ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                            </div>
                        </div>
                    </div>`;

                $('#ajax-alert-container').html(alertHtml);

                // Auto-dismiss après 5 secondes
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            $(function () {
                /**
                 * Image preview to upload
                 */
                $('#files_urls').on('change', function (e) {
                    // Récupérer les fichiers
                    const files = e.target.files;
                    const imagePreviewContainer = $('#image-preview-container');

                    // Effacer les vignettes existantes
                    imagePreviewContainer.empty();

                    // Créer une vignette pour chaque fichier sélectionné
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();

                        reader.onload = function (e) {

                            const imageUrl = e.target.result;
                            const fileName = file.name;

                            // Créer l'élément de la vignette avec la croix
                            const imageThumbnail = $(`
                            <div class="preview-thumbnail">
                                <img src="${imageUrl}" alt="${fileName}" />
                                <span class="remove-image">&times;</span>
                                </div>
                                `);

                            // Ajouter la vignette au conteneur
                            imagePreviewContainer.append(imageThumbnail);

                            // Gérer la suppression de l'image
                            imageThumbnail.find('.remove-image').on('click', function () {

                                // Supprimer le fichier de l'input
                                const fileList = Array.from($('#files_urls')[0].files);
                                const index = fileList.findIndex(f => f.name === fileName);

                                if (index !== -1) {
                                    fileList.splice(index, 1);
                                }

                                // Mettre à jour les fichiers de l'input
                                $('#files_urls')[0].files = new FileListItems(fileList);

                                // Supprimer la vignette de l'UI
                                imageThumbnail.remove();
                            });
                        };

                        reader.readAsDataURL(file);
                    });
                });

                /**
                 * Ajax to send
                 */
                /* Role form */
                $('#addRoleForm').on('submit', function (e) {
                    e.preventDefault();

                    // Afficher l'animation de chargement
                    $('#ajax-loader').removeClass('d-none');

                    // Effacer les alertes précédentes
                    $('#ajax-alert-container').empty();

                    var formData = new FormData(this);

                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (response) {
                            // Cacher l'animation de chargement
                            $('#ajax-loader').addClass('d-none');

                            // Afficher une alerte de succès
                            $('#ajax-alert-container').html(`<div class="row position-fixed w-100" style="opacity: 0.9; z-index: 99999;">
                                                                <div class="col-lg-4 col-sm-6 mx-auto">
                                                                    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                                                                        <i class="bi bi-info-circle me-2 fs-4" style="vertical-align: -3px;"></i> ${response.message || "__('notifications.added_data')"}
                                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                                                    </div>
                                                                </div>
                                                            </div>`);

                            // Réinitialiser tous les champs du formulaire
                            $('#addRoleForm')[0].reset();

                            location.reload();
                        },
                        error: function (error) {
                            // Cacher l'animation de chargement
                            $('#ajax-loader').addClass('d-none');

                            // Afficher une alerte d'erreur
                            $('#ajax-alert-container').html(`<div class="row position-fixed w-100" style="opacity: 0.9; z-index: 99999;">
                                                                <div class="col-lg-4 col-sm-6 mx-auto">
                                                                    <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                                                                        <i class="bi bi-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i> {{ __('notifications.error_while_processing') }}
                                                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                                                    </div>
                                                                </div>
                                                            </div>`);
                        }
                    });
                });

                // 🔄 Updates the hidden "assertion" field with the selected IDs
                $(document).on('change', '.assertion-checkbox', function () {
                    const selected = $('.assertion-checkbox:checked').map(function () {

                        return $(this).val();
                    }).get();

                    // We put a comma-separated list
                    $('#linked_assertion').val(selected.join(','));
                });
            });
        </script>
{{-- @if (Route::is('admin.home'))
        <script type="text/javascript">
            const ctx = document.getElementById('paymentsChart').getContext('2d');

            const chartData = {
                labels: @json($chartData['labels']), // Semaine 1, Semaine 2, ...
                datasets: [{
                    label: '{{ __("miscellaneous.admin.statistics.payment.title") . " " . strtolower(__("miscellaneous.period.adjectif.weekly_masculine")) }}',
                    data: @json($chartData['data']), // Nombre de paiements par semaine
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };

            const config = {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };

            const paymentsChart = new Chart(ctx, config);
        </script>
@endif --}}
@if (Route::is('admin.role.entity.home') AND $entity == 'users')
        <script type="text/javascript">
            /**
             * Update role
             */
            function changeUserRole(selectElement) {
                var roleId = selectElement.value;
                var entityId = 'users'; // Change cela si nécessaire pour d'autres entités
                var userId = selectElement.getAttribute('data-user-id'); // Assure-toi que l'ID de l'utilisateur est disponible dans le code JavaScript

                // Si la valeur sélectionnée est celle actuelle, ne rien faire
                if (!roleId || roleId === selectElement.getAttribute('data-user-role-id')) {
                    return;
                }

                Swal.fire({
                    title: "<?= __('miscellaneous.alert.attention.role') ?>",
                    text: "<?= __('miscellaneous.alert.confirm.role') ?>",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#04471a",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "<?= __('miscellaneous.alert.yes.role') ?>",
                    cancelButtonText: "<?= __('miscellaneous.cancel') ?>"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: headers,
                            type: "POST", // Utilise POST car c'est la méthode de la route
                            url: `${currentHost}/dashboard/role/${entityId}/${userId}`,
                            contentType: "application/json",
                            data: JSON.stringify({ role_id: roleId }),
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "<?= __('miscellaneous.alert.perfect') ?>",
                                        text: response.message,
                                        icon: "success"
                                    });
                                    location.reload();
                                } else {
                                    Swal.fire({
                                        title: "<?= __('miscellaneous.alert.oups') ?>",
                                        text: response.message,
                                        icon: "error"
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    title: "<?= __('miscellaneous.alert.oups') ?>",
                                    text: "Une erreur est survenue, veuillez réessayer plus tard.",
                                    icon: "error"
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "<?= __('miscellaneous.cancel') ?>",
                            text: "<?= __('miscellaneous.alert.canceled.role') ?>",
                            icon: "error"
                        });
                    }
                });
            }
        </script>
@endif
    </body>
</html>
