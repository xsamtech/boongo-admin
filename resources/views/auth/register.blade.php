@extends('layouts.auth', ['page_title' => __('auth.login')])

@section('auth-content')

                            <div class="creative-card-body card-body p-sm-5">
                                <h2 class="fs-20 fw-bolder mb-4 text-center">@lang('miscellaneous.register_title1')</h2>

                                <form method="POST" action="{{ route('register') }}">
    @csrf
                                    <div id="otherImageWrapper" class="row mb-3">
                                        <div class="col-sm-7 col-9 mx-auto position-relative">
                                            <p class="mb-1 text-center">Profil</p>

                                            <img src="{{ asset('assets/img/user.png') }}" alt="Avatar" class="other-user-image img-fluid img-thumbnail rounded-4">
                                            <label role="button" for="image_other" class="btn btn-danger position-absolute end-0 bottom-0 rounded-circle p-0" style="width: 3rem; height: 3rem;">
                                                <i class="bi bi-pencil-fill text-white fs-5"></i>
                                                <input type="file" name="image_other" id="image_other" class="d-none">
                                            </label>
                                            <input type="hidden" name="image_64" id="image_64">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="firstname" class="form-label">Prénom</label>
                                        <input type="text" name="firstname" class="form-control @error('firstname') is-invalid @enderror" id="firstname" value="{{ old('firstname') }}" autofocus>
    @error('firstname')
                                        <small class="text-danger d-inline-block mt-1 float-end">{{ $message }}</small>
    @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="lastname" class="form-label">Nom</label>
                                        <input type="text" name="lastname" class="form-control" id="lastname">
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">E-mail</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}">
    @error('email')
                                        <small class="text-danger d-inline-block mt-1 float-end">{{ $message }}</small>
    @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">N° de téléphone</label>
                                        <input type="text" name="phone" class="form-control" id="phone" value="{{ old('phone') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mot de passe</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password">
    @error('password')
                                        <small class="text-danger d-inline-block mt-1 float-end">{{ $message }}</small>
    @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmer mot de passe</label>
                                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation">
    @error('password_confirmation')
                                        <small class="text-danger d-inline-block mt-1 float-end">{{ $message }}</small>
    @enderror
                                    </div>

                                    <button type="submit" class="btn btn-success w-100 rounded-pill">Enregistrer</button>
                                    <a href="{{ route('login') }}" class="btn btn-secondary w-100 mt-2 rounded-pill text-white">Annuler</a>
                                </form>
                            </div>

@endsection
