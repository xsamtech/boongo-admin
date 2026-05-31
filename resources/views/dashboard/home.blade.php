@extends('layouts.guest')

@section('guest-content')
    <section class="py-5" style="margin-top: 90px;">
        <div class="container px-5">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-9 text-center">
                    <h1 class="display-5 fw-bold mb-3">{{ __('messages.home.title') }}</h1>
                    <p class="lead text-muted mb-0">{{ __('messages.home.description') }}</p>
                </div>
            </div>

            <div class="row g-4">
                @foreach ([
                    ['title' => __('messages.spaces.admin'), 'description' => __('messages.home.admin_description'), 'icon' => 'bi-shield-lock', 'url' => route('admin.home')],
                    ['title' => __('messages.spaces.manager'), 'description' => __('messages.home.manager_description'), 'icon' => 'bi-kanban', 'url' => route('manager.home')],
                    ['title' => __('messages.spaces.encoder'), 'description' => __('messages.home.encoder_description'), 'icon' => 'bi-pencil-square', 'url' => route('encoder.home')],
                ] as $space)
                    <div class="col-lg-4">
                        <a href="{{ $space['url'] }}" class="text-decoration-none">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-4">
                                    <div class="rounded-circle bg-warning bg-opacity-25 text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width:52px;height:52px;">
                                        <i class="bi {{ $space['icon'] }} fs-4"></i>
                                    </div>
                                    <h2 class="h4 text-dark mb-2">{{ $space['title'] }}</h2>
                                    <p class="text-muted mb-0">{{ $space['description'] }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
