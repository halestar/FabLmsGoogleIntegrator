@extends('layouts.class-settings', ['breadcrumb' => $breadcrumb, 'classSelected' => $classSelected])

@section('class_settings_content')
    <livewire:google-integrator.class-preferences :school-class="$classSelected" />
@endsection