@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedService' => $service])

@section('integrator-content')
    @if($service->canEnable())
    <form action="{{ route('integrators.google.services.auth.update') }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-3">{{ __('google-integrator::google.services.auth.settings') }}</h4>
            <livewire:utilities.model-switch
                    :model="$service"
                    property="enabled"
                    classes="col-2 ms-auto"
            />
        </div>
        <div class="form-check form-switch mb-3">
            <input
                    class="form-check-input"
                    @checked($service->data->use_avatar)
                    type="checkbox"
                    role="switch"
                    id="use_avatar"
                    name="use_avatar"
                    value="1"
                    switch
            />
            <label for="use_avatar" class="form-check-label">
                {{ __('google-integrator::google.services.auth.use_avatar') }}
            </label>
            <div id="use_avatar_help"
                 class="form-text">{!! __('google-integrator::google.services.auth.use_avatar.description')!!}</div>
        </div>
        <div class="mb-2">{{ __('google-integrator::google.services.auth.autoconnect') }}</div>
        <div class="row row-cols-lg-3 row-cols-md-2 mb-4">
            @foreach(\halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices::userServices() as $googleService)
                <div class="col form-check form-switch mb-3">
                    <input
                            class="form-check-input"
                            @checked(in_array($googleService->value, $service->data->autoconnect))
                            type="checkbox"
                            role="switch"
                            id="{{ $googleService->value }}"
                            name="autoconnect[]"
                            value="{{ $googleService->value }}"
                            switch
                    />
                    <label for="{{ $googleService->value }}" class="form-check-label">
                        {{ $googleService->label() }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="row">
            <button type="submit" class="btn btn-primary">{{ __('system.settings.update') }}</button>
        </div>
    </form>
    @else
        <div class="alert alert-danger">
            {{ __('google-integrator::google.services.disables.oauth') }}
        </div>
    @endif
@endsection