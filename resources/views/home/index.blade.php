@extends('layouts.marketing')

@section('content')
<main>
    @include('home.sections.hero')
    @include('home.sections.benefits')
    @include('home.sections.security')
    @include('home.sections.roles')
    @include('home.sections.organization')
    @include('home.sections.call-to-action')
</main>
@endsection
