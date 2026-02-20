@extends('layouts.guest', ['page_title' => (!empty($entity_title) ? $entity_title : __('miscellaneous.public.about.content.titles.0.title'))])

@section('guest-content')

    @if (Route::is('about.home'))
        @include('partials.about.about')
    @endif

    @if (Route::is('about.entity'))
        @include('partials.about.' . $entity)
    @endif

@endsection
