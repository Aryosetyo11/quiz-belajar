@extends('layouts.portal')

@section('title', 'Dashboard RuangUji')

@section('content')
@if ($role === \App\Models\User::ROLE_TEACHER)
    @include('portal.teacher-dashboard')
@else
    @include('portal.student-dashboard')
@endif
@endsection
