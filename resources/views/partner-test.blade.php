<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="@lang('miscellaneous.keywords')">
        <meta name="bng-url" content="{{ getWebURL() }}">
        <meta name="bng-api-url" content="{{ getApiURL() }}">
        <meta name="bng-visitor" content="{{ !empty(Auth::user()) ? Auth::user()->id : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="bng-ref" content="{{ !empty(Auth::user()) ? Auth::user()->api_token : null }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- ============ Favicon ============ -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('assets/img/favicon/site.webmanifest') }}">

        <!-- ============ Font Icons Files ============ -->
        {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> --}}
        <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">

 		<!-- ============ Google font ============ -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

        <!-- ============ Addons CSS Files ============ -->
 		<!-- Bootstrap -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/custom/mdb/css/mdb.min.css') }}"/>
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/custom/bootstrap/css/bootstrap.min.css') }}"/>
 		<!-- Slick -->
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/cooladmin/slick/slick.css') }}"/>
 		<link type="text/css" rel="stylesheet" href="{{ asset('assets/addons/cooladmin/slick/slick-theme.css') }}"/>
 		<!-- Other -->
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/perfect-scrollbar/css/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/dataTables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/cropper/css/cropper.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/addons/custom/wysiwyg-editor-master/css/froala_editor.min.css') }}">

 		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
 		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
 		<!--[if lt IE 9]>
 		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
 		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
 		<![endif]-->

        <!-- ============ Custom CSS ============ -->
        <link type="text/css" rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}">

        <title>@lang('miscellaneous.admin.partner.add')</title>
    </head>
	<body>
        <span class="menu-sidebar2__content d-none"></span>
        <!-- MODALS-->
        <!-- ### Crop user image ### -->
        <div class="modal fade" id="cropModalUser" tabindex="-1" aria-labelledby="cropModalUserLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalUserLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 mb-sm-0 mb-4">
                                    <div class="bg-image">
                                        <img src="" id="retrieved_image" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light border rounded-pill" data-bs-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_avatar" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ### Crop other image ### -->
        <div class="modal fade" id="cropModalOther" tabindex="-1" aria-labelledby="cropModalOtherLabel" aria-hidden="true" data-bs-backdrop="{{ Route::is('branch.home') ? 'static' : 'true' }}">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cropModalOtherLabel">{{ __('miscellaneous.crop_before_save') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                        <button type="button" class="btn btn-light border rounded-pill" data-bs-dismiss="modal">@lang('miscellaneous.cancel')</button>
                        <button type="button" id="crop_other" class="btn btn-primary rounded-pill" data-bs-dismiss="modal">{{ __('miscellaneous.register') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MODALS-->

@if (\Session::has('success_message'))
        <!-- ALERT-->
        <div class="position-relative">
            <div class="row position-absolute w-100" style="top: 0; opacity: 0.9; z-index: 9999;">
                <div class="col-lg-5 col-sm-6 mx-auto mt-lg-0 mt-5">
                    <div class="alert alert-success alert-dismissible fade show rounded-0" role="alert">
                        <i class="fa-solid fa-info-circle me-2 fs-4" style="vertical-align: -3px;"></i> {{ \Session::get('success_message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ALERT END-->
@endif

@if (\Session::has('error_message'))
        <!-- ALERT-->
        <div class="position-relative">
            <div class="row position-absolute w-100" style="top: 0; opacity: 0.9; z-index: 9999;">
                <div class="col-lg-5 col-sm-6 mx-auto mt-lg-0 mt-5">
                    <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i> {{ explode('~', \Session::get('error_message'))[3]  }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ALERT END-->
@endif

        <div class="py-4">
            <div class="d-flex justify-content-center mb-1">
                <img src="{{ asset('assets/img/brand-reverse.png') }}" alt="Boongo" width="200">
            </div>

            <div class="container-lg container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-lg-11 d-sm-flex justify-content-between align-items-center mx-auto mb-4">
                                <h1 class="m-sm-0 mt-3 mb-1 text-sm-start text-center">@lang('miscellaneous.admin.partner.add')</h1>
                                <p class="m-0 text-sm-start text-center"><a href="{{ route('home', ['admin' => 'y']) }}">@lang('miscellaneous.admin.work.link')</a></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-sm-6">
                        {{-- <form action="{{ route('admin.work.home') }}" method="post" enctype="multipart/form-data"> --}}
                        <form id="partnerData">
@csrf

                            <div class="card mb-4">
                                <div class="card-body pb-0">
                                    <div class="form-group">
                                        <label for="name" class="visually-hidden">@lang('miscellaneous.admin.partner.data.name')</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="@lang('miscellaneous.admin.partner.data.name')" autofocus>
                                    </div>

                                    <div class="form-group mt-2">
                                        <label for="website_url" class="visually-hidden">@lang('miscellaneous.admin.partner.data.website_url')</label>
                                        <input type="text" name="website_url" id="website_url" class="form-control" placeholder="@lang('miscellaneous.admin.partner.data.website_url')">
                                    </div>
                                </div>

                                <div id="otherImageWrapper" class="card-body pb-4 text-center">
                                    <p class="card-text m-0">@lang('miscellaneous.account.personal_infos.click_to_change_picture')</p>

                                    <div class="bg-image hover-overlay mt-2 mb-3">
                                        <img src="{{ asset('assets/img/ad.png') }}" alt="@lang('miscellaneous.admin.partner.data.name')" class="other-user-image img-fluid rounded-4">
                                        <div class="mask rounded-4" style="background-color: rgba(5, 5, 5, 0.5);">
                                            <label role="button" for="image_other" class="d-flex h-100 justify-content-center align-items-center">
                                                <i class="fa-solid fa-pencil-alt text-white fs-2"></i>
                                                <input type="file" name="image_other" id="image_other" class="d-none">
                                            </label>
                                            <input type="hidden" name="image_64" id="image_64">
                                        </div>
                                    </div>

                                    <p class="d-none mb-3 mb-0 small bng-text-primary fst-italic">@lang('miscellaneous.waiting_register')</p>

                                    <button class="btn btn-block btn-primary">@lang('miscellaneous.register')</button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-5 text-center request-message"></div>
                        </form>
                    </div>

                    <div class="col-lg-7 col-sm-6">
                        <div class="card border">
                            <div class="card-header d-sm-flex justify-content-between align-items-center">
                                <h3 class="m-sm-0 mb-1">@lang('miscellaneous.admin.partner.list')</h3>
                                <div id="loading" class="spinner-border text-primary d-none" role="status"><span class="visually-hidden">Loading...</span></div>
                            </div>
@if (count($partners) > 0)
                            <ul class="list-group list-group-flush">
    @foreach ($partners as $item)
                                <li class="list-group-item py-3">
                                    <img src="{{ !empty($item->image_url) ? $item->image_url : asset('assets/img/cover.png') }}" alt="{{ $item->name }}" width="160" class="float-sm-start rounded-2 mb-sm-0 mb-3 me-3">
                                    <h4 class="m-0">{{ $item->name }}</h4>
                                    <div class="form-check form-switch float-end">
                                        <input class="form-check-input" type="checkbox" role="switch" data-value="{{ $item->is_active }}" id="is_active-{{ $item->id }}" onchange="changeStatus(this)" {{ $item->is_active == 1 ? 'checked' : '' }} />
                                        <label class="form-check-label" for="is_active-{{ $item->id }}">{{ $item->is_active == 1 ? __('miscellaneous.active') : __('miscellaneous.inactive') }}</label>
                                    </div>
                                </li>
    @endforeach
                            </ul>

    {{-- @if ($lastPage > 1)
                            <div class="card-body pb-0 d-flex justify-content-center">
        @include('partials.pagination')
                            </div>
    @endif --}}
@else
                            <div class="card-body text-center">
                                <p class="m-0 lead text-secondary fst-italic">@lang('miscellaneous.empty_list')</p>
                            </div>
@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <span id="btnBackTop" class="btn btn-floating btn-primary pb-0 d-none" style="position: fixed; bottom: 2rem; right: 2rem;"><i class="fa-solid fa-chevron-up"></i></span>

        <!-- jQuery Plugins -->
        <script src="{{ asset('assets/addons/custom/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/mdb/js/mdb.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/bootstrap/js/bootstrap.min.js') }}"></script>
		<script src="{{ asset('assets/addons/cooladmin/slick/slick.min.js') }}"></script>
		<script src="{{ asset('assets/addons/custom/jquery/jquery.zoom/jquery.zoom.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/perfect-scrollbar/dist/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/autosize/js/autosize.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/dataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/cropper/js/cropper.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/sweetalert2/dist/sweetalert2.min.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/jquery/scroll4ever/js/jquery.scroll4ever.js') }}"></script>
        <script src="{{ asset('assets/addons/custom/wysiwyg-editor-master/js/froala_editor.min.js') }}"></script>
		<script src="{{ asset('assets/js/script.custom.js') }}"></script>
		<script type="text/javascript">
            /* Change status of an entity */
            function changeStatus(element) {
                var _this = document.getElementById(element.id);
                var _this_value = parseInt(_this.getAttribute("data-value"));
                var _this_id = parseInt(_this.id.split('-')[1]);

                if (_this_value == 1) {
                    _this.setAttribute("data-value", 0);

                    $.ajax({
                        headers: { 'Accept': 'application/json', 'X-localization': navigator.language },
                        type: "PUT",
                        contentType: "application/json",
                        url: apiHost + "/partner/" + _this_id,
                        dataType: "json",
                        data: JSON.stringify({ "id" : _this_id, "is_active" : 0 }),
                        beforeSend: function () {
                            $('#loading').removeClass('d-none');
                        },
                        success: function (res) {
                            $('#loading').addClass('d-none');
                            location.reload();
                        },
                        error: function (xhr, error, status_description) {
                            console.log(xhr.responseJSON);
                            console.log(xhr.status);
                            console.log(error);
                            console.log(status_description);
                        }
                    });

                } else {
                    _this.setAttribute("data-value", 1);

                    $.ajax({
                        headers: { 'Accept': 'application/json', 'X-localization': navigator.language },
                        type: "PUT",
                        contentType: "application/json",
                        url: apiHost + "/partner/" + _this_id,
                        dataType: "json",
                        data: JSON.stringify({ "id" : _this_id, "is_active" : 1 }),
                        beforeSend: function () {
                            $('#loading').removeClass('d-none');
                        },
                        success: function (res) {
                            $('#loading').addClass('d-none');
                            location.reload();
                        },
                        error: function (xhr, error, status_description) {
                            console.log(xhr.responseJSON);
                            console.log(xhr.status);
                            console.log(error);
                            console.log(status_description);
                        }
                    });
                }
            }

            $(function () {
                /* Register form-data */
                $('form#partnerData').submit(function (e) {
					e.preventDefault();

                    var formData = new FormData(this);

                    $.ajax({
						headers: { 'Accept': 'application/json', 'X-localization': navigator.language },
						type: 'POST',
						contentType: 'application/json',
						url: apiHost + '/partner',
						data: formData,
						beforeSend: function () {
							$('form#partnerData .request-message').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
						},
						success: function (res) {
                            if ($('form#partnerData .request-message').hasClass('text-danger')) {
                                $('form#partnerData .request-message').removeClass('text-danger');
                            }

							$('form#partnerData .request-message').addClass('text-success').html(res.message);

                            document.getElementById('partnerData').reset();
							location.reload();
                        },
						cache: false,
						contentType: false,
						processData: false,
						error: function (xhr, error, status_description) {
                            if ($('form#partnerData .request-message').hasClass('text-success')) {
                                $('form#partnerData .request-message').removeClass('text-success');
                            }

                            $('form#partnerData .request-message').addClass('text-danger').html(xhr.responseJSON.message);
							console.log(xhr.responseJSON);
							console.log(xhr.status);
							console.log(error);
							console.log(status_description);
						}
					});
				});
            });
        </script>
	</body>
</html>
