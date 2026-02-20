<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="bng-url" content="{{ getWebURL() }}">
        <meta name="bng-api-url" content="{{ getApiURL() }}">
        <meta name="bng-visitor" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bng-ref" content="">

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
			Boongo Administration
@endif
		</title>
    </head>

    <body>
        <!-- MODALS-->
        <!-- ### Crop other user image ### -->
        <div class="modal fade" id="cropModalOther" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header py-0">
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="text-center text-muted">Recadrer l'image avant de l'enregistrer</h5>

                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-sm-0 mb-4">
                                    <div class="bg-image">
                                        <img src="" id="retrieved_image_other" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill text-white" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" id="crop_other" class="btn btn-primary px-4 rounded-pill" data-bs-dismiss="modal">Enregistrer</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODALS-->

        <!--! ================================================================ !-->
        <!--! [Start] Main Content !-->
        <!--! ================================================================ !-->
        <main class="auth-minimal-wrapper menu-sidebar2__content">
            <div class="auth-minimal-inner">
                <div class="minimal-card-wrapper">
                    <div class="card mb-4 mt-5 mx-4 mx-sm-0 position-relative">
                        <div class="wd-50 bg-white p-2 rounded-circle shadow-lg position-absolute translate-middle top-0 start-50">
                            <img src="{{ asset('assets/img/logo.png') }}" alt="" class="img-fluid">
                        </div>

@yield('auth-content')
                    </div>
                </div>
            </div>
        </main>
        <!--! ================================================================ !-->
        <!--! [End] Main Content !-->
        <!--! ================================================================ !-->

        <!-- ============ JavaScript ============ -->
        <!-- jQuery JS -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <!-- Bootstrap core JS -->
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> --}}
        <!-- Vendors JS -->
        <script src="{{ asset('templates/admin/assets/vendors/js/vendors.min.js') }}"></script>
        <!--Apps Init -->
        <script src="{{ asset('templates/admin/assets/js/common-init.min.js') }}"></script>
        <!-- Theme Customizer -->
        <script src="{{ asset('templates/admin/assets/js/theme-customizer-init.min.js') }}"></script>
        <!-- Autosize JS-->
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/js/script.custom.js') }}"></script>
    </body>
</html>
