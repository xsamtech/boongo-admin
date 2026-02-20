@extends('layouts.guest')

@section('guest-content')

                <!-- SECTION -->
                <div class="section">
                    <!-- container -->
                    <div class="container" style="padding: 0 3rem;">
                        <!-- row -->
                        <div id="subscribe" class="row">
                            <div class="col-12">
                                <div class="text-center">
                                    <img src="{{ asset('assets/img/logo.png') }}" alt="Boongo" width="60">
                                </div>

                                <div class="center-block" style="max-width: 40rem; min-height: 50rem;">
                                    <form method="POST" action="{{ route('subscribe') }}">
                                        <input type="hidden" name="app_url" value="{{ getWebURL() }}">
                                        <input type="hidden" name="user_id" value="{{ request()->get('user_id') }}">
                                        <input type="hidden" name="api_token" value="{{ request()->get('api_token') }}">
                                        <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
    @csrf
                                        <div class="text-center" style="margin: 2rem 0;">
                                            <h3 class="text-uppercase fw-bolder">@lang('miscellaneous.menu.admin.subscription')</h3>
                                            <h5 class="text-muted" style="font-weight: 600; margin-bottom: 5rem;">@lang('miscellaneous.public.about.subscribe.send_money.description')</h5>

                                            <div class="text-center" style="background-color: #fea; margin-bottom: 2rem; padding: 1rem; border-radius: 2rem;">
                                                <p style="margin: 0; font-size: 1.2rem;">
                                                    @lang('miscellaneous.public.about.subscribe.choosen_subscription') <strong>{{ $subscription_type }}</strong>
                                                </p>
                                                <p class="lead" style="margin: 0; font-weight: 800;">{{ round($subscription->price) }} $</p>
                                            </div>

                                            <div id="paymentMethod" style="padding-left: 1rem;">
    @foreach ($transaction_types as $type)
        @if ($type['type_name'] == __('miscellaneous.public.about.subscribe.send_money.mobile_money'))
                                                <label class="radio" style="margin-bottom: 1rem;">
                                                    <input type="radio" name="transaction_type_id" id="mobile_money" value="{{ $type['id'] }}" style="vertical-align: middle;">
                                                    <img src="{{ asset('assets/img/payment-mobile-money.png') }}" alt="{{ __('miscellaneous.public.about.subscribe.send_money.mobile_money') }}" width="40">
                                                    @lang('miscellaneous.public.about.subscribe.send_money.mobile_money')
                                                </label>
        @else
                                                <label class="radio">
                                                    <input type="radio" name="transaction_type_id" id="bank_card" value="{{ $type['id'] }}" style="vertical-align: middle;">
                                                    <img src="{{ asset('assets/img/payment-credit-card.png') }}" alt="{{ __('miscellaneous.public.about.subscribe.send_money.bank_card') }}" width="40">
                                                    @lang('miscellaneous.public.about.subscribe.send_money.bank_card')
                                                </label>
        @endif
    @endforeach
                                            </div>
                                        </div>

                                        <div id="phoneNumberForMoney">
                                            <select name="select_country" id="select_country1" class="form-control" style="margin: 1rem 0;">
                                                <option style="font-size: 0.9rem;" selected disabled>@lang('miscellaneous.choose_country')</option>
    @forelse ($countries as $country)
                                                <option value="{{ $country['country_phone_code'] . '-' . $country['id'] }}">{{ $country['country_name'] }}</option>
    @empty
                                                <option>@lang('miscellaneous.empty_list')</option>
    @endforelse
                                            </select>

                                            <div class="form-group" style="margin-bottom: 2rem;">
                                                <label class="sr-only" for="phone_number">@lang('miscellaneous.phone_code')</label>
                                                <div id="phone_code_text1" class="input-group">
                                                    <div class="input-group-addon text-value">xxxx</div>
                                                    <input type="tel" name="other_phone_number" id="phone_number" class="form-control" placeholder="@lang('miscellaneous.phone')">
                                                    <input type="hidden" id="phone_code1" name="other_phone_code" value="">
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn btn-block bng-btn-success" type="submit" onclick="document.getElementById('loading').style.display = 'block';">@lang('miscellaneous.send')</button>

                                        <div id="loading" class="d-flex justify-content-center text-center" style="display: none; margin-top: 1rem;">
                                            <img src="{{ asset('assets/img/ajax-loading.gif') }}" alt="" width="70">
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                        {{-- <div class="row">
                        </div> --}}
                        <!-- /row -->
                    </div>
                    <!-- /container -->
                </div>
                <!-- /SECTION -->

@endsection
