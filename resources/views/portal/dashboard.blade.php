@extends('layouts.portal')

@section('title', 'Dashboard RuangUji')

@section('content')
@if ($role === \App\Models\User::ROLE_TEACHER)
    @include('portal.teacher-dashboard')
@elseif ($role === \App\Models\User::ROLE_STUDENT)
    @include('portal.student-dashboard')
@else
    @include('portal.admin-dashboard')
@endif
@endsection
