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

        <!-- Bootstrap icons-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <!-- Google fonts-->
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link rel="stylesheet" href="{{ asset('templates/public/css/styles.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/style.custom.css') }}" />

        <title>
@if (!empty($page_title))
			{{ $page_title }}
@else
			Boongo
@endif
		</title>
    </head>

    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light{{ Route::is('home') ?' fixed-top' : ' mb-4' }} shadow-sm{{ request()->has('app') ? ' d-none' : '' }}" id="mainNav">
            <div class="container px-5">
                <a class="navbar-brand fw-bold" href="/">
                    <img src="{{ asset('assets/img/brand.png') }}" alt="KinTaxi" width="200px">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu <i class="bi-list"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                        <li class="nav-item"><a class="nav-link me-lg-3" href="/#features">Fontionnalités</a></li>
                        <li class="nav-item"><a class="nav-link me-lg-3" href="/#download">Téléchargement</a></li>
                    </ul>
                    <button class="btn btn-warning px-3 mb-2 mb-lg-0 rounded-pill" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                        <span class="d-flex align-items-center">
                            <i class="bi-chat-text-fill me-2"></i>
                            <span class="small">Contact</span>
                        </span>
                    </button>
                </div>
            </div>
        </nav>

@if (\Session::has('error_message'))
        <!-- Alert Start -->
        <div class="position-relative">
            <div class="row position-fixed w-100" style="top: 70px; z-index: 9999;">
                <div class="col-lg-4 col-sm-6 mx-auto">
                    <div class="alert alert-danger alert-dismissible fade show rounded-0" role="alert">
                        <i class="bi bi-exclamation-triangle me-2 fs-4" style="vertical-align: -3px;"></i> {!! \Session::get('error_message') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Alert End -->
@endif

@yield('guest-content')

        <!-- App badge section-->
        <section class="bg-gradient-primary-to-secondary{{ request()->has('app') ? ' d-none' : '' }}" id="download">
            <div class="container px-5">
                <h2 class="text-center text-white font-alt mb-4">Téléchargez l'appli maintenant !</h2>

                <form action="{{ route('download') }}" method="POST" class="mb-3 text-center">
    @csrf
                    <button class="btn btn-light">
                        <i class="bi bi-android me-2 fs-5 align-middle"></i> Télécharger sur le site
                    </button>
                </form>

                <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center">
                    <a class="me-lg-3 mb-4 mb-lg-0" href="#!">
                        <img class="app-badge" src="{{ asset('templates/public/assets/img/google-play-badge.svg') }}" alt="..." />
                    </a>
                    <a href="#!">
                        <img class="app-badge" src="{{ asset('templates/public/assets/img/app-store-badge.svg') }}" alt="..." />
                    </a>
                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="bg-black text-center py-5{{ request()->has('app') ? ' d-none' : '' }}">
            <div class="container px-5">
                <div class="text-white-50 small">
                    <div class="mb-2">
                        &copy; {{ date('Y') }} Reborn. Tous droits réservés.
                        <span class="mx-1">&middot;</span>
                        Designed by <a href="https://xsamtech.com" target="_blank">Xsam Technologies</a>
                    </div>
                    <a href="{{ route('about.home') }}">@lang('miscellaneous.public.about.title')</a>
                    <span class="mx-1">&middot;</span>
                    <a href="{{ route('about.entity', ['entity' => 'privacy_policy']) }}">@lang('miscellaneous.public.about.privacy_policy.title')</a>
                    <span class="mx-1">&middot;</span>
                    <a href="{{ route('about.entity', ['entity' => 'terms_of_use']) }}">@lang('miscellaneous.public.about.terms_of_use.title')</a>
                    <span class="mx-1">&middot;</span>
                    <a href="{{ route('about.entity', ['entity' => 'faq']) }}">FAQ</a>
                </div>
            </div>
        </footer>
        <!-- Feedback Modal-->
        <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary-to-secondary p-4">
                        <h5 class="modal-title font-alt text-white" id="feedbackModalLabel">Donnez votre avis</h5>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body border-0 p-4">
                        <!-- * * * * * * * * * * * * * * *-->
                        <!-- * * SB Forms Contact Form * *-->
                        <!-- * * * * * * * * * * * * * * *-->
                        <!-- This form is pre-integrated with SB Forms.-->
                        <!-- To make this form functional, sign up at-->
                        <!-- https://startbootstrap.com/solution/contact-forms-->
                        <!-- to get an API token!-->
                        <form id="contactForm" data-sb-form-api-token="API_TOKEN">
                            <!-- Name input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text"
                                    placeholder="Enter your name..." data-sb-validations="required" />
                                <label for="name">Nom complet</label>
                                <div class="invalid-feedback" data-sb-feedback="name:required">Le nom est obligatoire.</div>
                            </div>
                            <!-- Email address input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" placeholder="name@example.com"
                                    data-sb-validations="required,email" />
                                <label for="email">Adresse e-mail</label>
                                <div class="invalid-feedback" data-sb-feedback="email:required">Le mail est obligatoire.
                                </div>
                                <div class="invalid-feedback" data-sb-feedback="email:email">Ce mail n'est pas valide.</div>
                            </div>
                            <!-- Phone number input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" placeholder="(123) 456-7890"
                                    data-sb-validations="required" />
                                <label for="phone">N° de téléphone</label>
                                <div class="invalid-feedback" data-sb-feedback="phone:required">Le n° de téléphone est obligatoire.</div>
                            </div>
                            <!-- Message input-->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="message" type="text" placeholder="Enter your message here..."
                                    style="height: 10rem" data-sb-validations="required"></textarea>
                                <label for="message">Message</label>
                                <div class="invalid-feedback" data-sb-feedback="message:required">Le message est obligatoire.
                                </div>
                            </div>
                            <!-- Submit success message-->
                            <!---->
                            <!-- This is what your users will see when the form-->
                            <!-- has successfully submitted-->
                            <div class="d-none" id="submitSuccessMessage">
                                <div class="text-center mb-3">
                                    <div class="fw-bolder">Message envoyé!</div>
                                </div>
                            </div>
                            <!-- Submit error message-->
                            <!---->
                            <!-- This is what your users will see when there is-->
                            <!-- an error submitting the form-->
                            <div class="d-none" id="submitErrorMessage">
                                <div class="text-center text-danger mb-3">Erreur d'envoi de message!</div>
                            </div>
                            <!-- Submit Button-->
                            <div class="d-grid">
                                <button class="btn btn-warning rounded-pill btn-lg" id="submitButton" type="submit">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <!-- * *                               SB Forms JS                               * *-->
        <!-- * * Activate your form at https://startbootstrap.com/solution/contact-forms * *-->
        <!-- * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *-->
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>
