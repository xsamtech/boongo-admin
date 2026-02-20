
		<!-- SECTION -->
		<div class="section py-4">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-3 mb-4{{ request()->has('app') ? ' d-none' : '' }}">
                        <div class="card card-body p-0 overflow-hidden">
                            <div class="list-group list-group-flush">
                                <a class="list-group-item list-group-item-action active">@lang('miscellaneous.menu.about')</a>
                                <a href="{{ route('about.entity', ['entity' => 'privacy_policy']) }}" class="list-group-item list-group-item-action">@lang('miscellaneous.menu.privacy_policy')</a>
                                <a href="{{ route('about.entity', ['entity' => 'terms_of_use']) }}" class="list-group-item list-group-item-action">@lang('miscellaneous.menu.terms_of_use')</a>
                                <a href="{{ route('about.entity', ['entity' => 'faq']) }}" class="list-group-item list-group-item-action">FAQ</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9 px-4">
@foreach ($titles as $ttl)
                        <h1 class="h1 mb-5 fw-bold">{{ $ttl['title'] }}</h1>

    @foreach ($ttl['contents'] as $cnt)
                        <div class="mb-4">
                            <h3 class="h3 mb-3 fw-semibold">{{ $cnt['subtitle'] }}</h3>

                            <p class="m-0 fs-6" style="color: #444; text-align: justify;">{!! $cnt['content'] !!}</p>
                        </div>
    @endforeach
@endforeach
                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
