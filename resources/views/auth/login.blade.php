@extends('layouts.auth', ['page_title' => __('auth.login')])

@section('auth-content')

                            <div class="creative-card-body card-body p-sm-5">
                                <h2 class="fs-20 fw-bolder mb-4">@lang('miscellaneous.login_title2')</h2>
                                <h4 class="fs-13 fw-bold mb-2">@lang('miscellaneous.login_title1')</h4>
                                <p class="fs-12 fw-medium text-muted">@lang('miscellaneous.login_description')</p>

                                <form method="POST" action="{{ route('login') }}">
    @csrf
    @if ($errors->has('login'))
                                    <div class="mb-0">
    @else
                                    <div class="mb-3">
    @endif
                                        <label for="login" class="form-label">@lang('miscellaneous.login_username')</label>
                                        <input type="text" name="login" id="login" class="form-control @error('login') is-invalid m-0 @enderror" placeholder="@lang('miscellaneous.login_username')" value="{{ old('login') }}" required @error('login') autofocus @enderror>
    @error('login')
                                        <small class="d-inline-block w-100 p-0 text-danger text-end">{{ $message }}</small>
    @enderror
                                    </div>

    @if ($errors->has('password'))
                                    <div class="mb-0">
    @else
                                    <div class="mb-3">
    @endif
                                        <label for="password" class="form-label">@lang('miscellaneous.password.label')</label>
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid m-0 @enderror" placeholder="@lang('miscellaneous.password.label')" required @error('password') autofocus @enderror>
    @error('password')
                                        <small class="d-inline-block w-100 p-0 text-danger text-end">{{ $message }}</small>
    @enderror
                                    </div>

                                    <div class="mb-3 form-check d-flex justify-content-center">
                                        <input type="checkbox" name="remember" id="remember" class="form-check-input me-2">
                                        <label role="button" class="form-check-label" for="remember">@lang('miscellaneous.remember_me')</label>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 rounded-pill">@lang('auth.login')</button>
    @if (!$admins_exist)
                                    <a href="{{ route('register') }}" class="btn btn-secondary w-100 mt-2 rounded-pill text-white">@lang('miscellaneous.register_title2')</a>
    @endif
                                </form>
                            </div>

@endsection
