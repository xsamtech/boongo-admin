
		<!-- BREADCRUMB -->
		<div id="breadcrumb" class="section detect-webview">
			<!-- container -->
			<div class="container">
				<!-- row -->
				<div class="row">
					<div class="col-md-12">
						<h3 class="breadcrumb-header">
@if (Route::is('home'))
                            @lang('miscellaneous.welcome_title')
@endif
@if (Route::is('about.home'))
                            @lang('miscellaneous.menu.about')
@endif
@if (Route::is('book.home'))
                            @lang('miscellaneous.menu.public.books')
@endif
@if (Route::is('newspaper.home'))
                            @lang('miscellaneous.menu.public.mag_newspapers')
@endif
@if (Route::is('map.home'))
                            @lang('miscellaneous.menu.public.mapping')
@endif
@if (Route::is('media.home'))
                            @lang('miscellaneous.menu.public.medias')
@endif
@if (Route::is('about.entity') || Route::is('account.entity') || Route::is('book.datas') || Route::is('newspaper.datas') || Route::is('map.datas') || Route::is('media.datas'))
                            {{ $entity_title }}
@endif
                        </h3>

@if (Route::is('about.home'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li class="active">@lang('miscellaneous.menu.about')</li>
						</ul>
@endif
@if (Route::is('about.entity'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li><a href="{{ route('about.home') }}">@lang('miscellaneous.menu.about')</a></li>
							<li class="active">{{ $entity_menu }}</li>
						</ul>
@endif
@if (Route::is('book.home'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li class="active">@lang('miscellaneous.menu.public.books')</li>
						</ul>
@endif
@if (Route::is('book.datas'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li><a href="{{ route('book.home') }}">@lang('miscellaneous.menu.public.books')</a></li>
							<li class="active">{{ $entity_menu }}</li>
						</ul>
@endif
@if (Route::is('newspaper.home'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li class="active">@lang('miscellaneous.menu.public.mag_newspapers')</li>
						</ul>
@endif
@if (Route::is('newspaper.datas'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li><a href="{{ route('newspaper.home') }}">@lang('miscellaneous.menu.public.mag_newspapers')</a></li>
							<li class="active">{{ $entity_menu }}</li>
						</ul>
@endif
@if (Route::is('map.home'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li class="active">@lang('miscellaneous.menu.public.mapping')</li>
						</ul>
@endif
@if (Route::is('map.datas'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li><a href="{{ route('map.home') }}">@lang('miscellaneous.menu.public.mapping')</a></li>
							<li class="active">{{ $entity_menu }}</li>
						</ul>
@endif
@if (Route::is('media.home'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li class="active">@lang('miscellaneous.menu.public.medias')</li>
						</ul>
@endif
@if (Route::is('media.datas'))
                        <ul class="breadcrumb-tree">
							<li><a href="{{ route('home') }}">@lang('miscellaneous.menu.home')</a></li>
							<li><a href="{{ route('media.home') }}">@lang('miscellaneous.menu.public.medias')</a></li>
							<li class="active">{{ $entity_menu }}</li>
						</ul>
@endif
					</div>
				</div>
				<!-- /row -->
			</div>
			<!-- /container -->
		</div>
		<!-- /BREADCRUMB -->
