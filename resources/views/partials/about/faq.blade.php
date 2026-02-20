
		<!-- SECTION -->
		<div class="section py-4">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
                    <div class="col-sm-3 mb-4{{ request()->has('app') ? ' d-none' : '' }}">
                        <div class="card card-body p-0 overflow-hidden">
                            <div class="list-group list-group-flush">
                                <a href="{{ route('about.home') }}" class="list-group-item list-group-item-action">@lang('miscellaneous.menu.about')</a>
                                <a href="{{ route('about.entity', ['entity' => 'privacy_policy']) }}" class="list-group-item list-group-item-action">@lang('miscellaneous.menu.privacy_policy')</a>
                                <a href="{{ route('about.entity', ['entity' => 'terms_of_use']) }}" class="list-group-item list-group-item-action">@lang('miscellaneous.menu.terms_of_use')</a>
                                <a class="list-group-item list-group-item-action active">FAQ</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9 px-4">

                    </div>
                </div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /SECTION -->
