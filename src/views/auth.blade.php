@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedService' => $service])

@section('integrator-content')
    @inject('vault', 'App\Classes\Integrators\SecureVault')

    <div class="alert alert-warning mb-3">{!! __('integrators.vault.warning') !!}</div>


    <form action="{{ route('integrators.google.services.oauth.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="card mb-3" x-data="{ expanded: false }">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">
                    <i class="fa-solid fa-chevron-right me-3 show-as-action" @click="expanded = true" x-show="!expanded"></i>
                    <i class="fa-solid fa-chevron-down me-3 show-as-action" @click="expanded = false" x-show="expanded"></i>
                    {{ __('google-integrator::google.oauth') }}
                </h4>
                @if($service->integrator->hasOauthCredentials())
                    <span class="badge bg-success">{{ __('common.enabled') }}</span>
                @else
                    <span class="badge bg-danger">{{ __('common.disabled') }}</span>
                @endif
            </div>
        <div class="card-body" x-show="expanded">
                <div class="alert alert-info mb-3">{!!  __('google-integrator::google.oauth.description') !!}</div>
                <div class="mb-3">
                    <label for="client_id" class="form-label">
                        {{ __('google-integrator::google.client_id') }}

                        @if($vault->hasKey('google', 'client_id'))
                            <i class="fs-6 ms-2 text-success fa-solid fa-check-circle"></i>
                        @else
                            <i class="fs-6 ms-2 text-danger fa-solid fa-times"></i>
                        @endif
                    </label>
                    <input
                            type="text"
                            class="form-control"
                            id="client_id"
                            name="client_id"
                            aria-describedby="client_id_help"
                    />
                    <div id="client_id_help"
                         class="form-text">{!! __('google-integrator::google.client_id.description')!!}</div>
                </div>
                <div class="mb-3">
                    <label for="client_secret" class="form-label">
                        {{ __('google-integrator::google.client_secret') }}

                        @if($vault->hasKey('google', 'client_secret'))
                            <i class="fs-6 ms-2 text-success fa-solid fa-check-circle"></i>
                        @else
                            <i class="fs-6 ms-2 text-danger fa-solid fa-times"></i>
                        @endif
                    </label>
                    <input
                            type="text"
                            class="form-control"
                            id="client_secret"
                            name="client_secret"
                            aria-describedby="client_secret_help"
                    />
                    <div id="client_secret_help"
                         class="form-text">{!! __('google-integrator::google.client_secret.description')!!}</div>
                </div>

                <div class="mb-3">
                    <label for="redirect" class="form-label">
                        {{ __('google-integrator::google.redirect') }}

                        @if($vault->hasKey('google', 'redirect'))
                            <i class="fs-6 ms-2 text-success fa-solid fa-check-circle"></i>
                        @else
                            <i class="fs-6 ms-2 text-danger fa-solid fa-times"></i>
                        @endif
                    </label>
                    <input
                            type="text"
                            class="form-control"
                            id="redirect"
                            name="redirect"
                            aria-describedby="redirect_help"
                            readonly
                            disabled
                            value="{{ $vault->retrieve('google', 'redirect') }}"
                    />
                    <div id="redirect_help" class="form-text">{!! __('google-integrator::google.redirect.description')!!}</div>
                </div>
            </div>
            <div class="card-footer" x-show="expanded">
                <div class="row">
                    <button type="submit" class="col btn btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </form>


    <form action="{{ route('integrators.google.services.auth.update') }}" method="post">
        @csrf
        @method('PATCH')
        <div class="card mb-3">
            <div class="card-body">
                @if(!$service->integrator->hasOauthCredentials())
                    <div class="alert alert-warning">
                        {{ __('google-integrator::google.services.auth.allow_user_connection.warning') }}
                    </div>
                @endif
                <div class="form-check form-switch mb-3">
                    <input
                            class="form-check-input"
                            @checked($service->integrator->hasOauthCredentials() && $service->data->allow_user_connection)
                            type="checkbox"
                            role="switch"
                            id="allow_user_connection"
                            name="allow_user_connection"
                            value="1"
                            switch
                            @disabled(!$service->integrator->hasOauthCredentials())
                    />
                    <label for="allow_user_connection" class="form-check-label">
                        {{ __('google-integrator::google.services.auth.allow_user_connection') }}
                    </label>
                    <div id="allow_user_connection_help"
                         class="form-text">{!! __('google-integrator::google.services.auth.allow_user_connection.description')!!}</div>
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
                            @disabled(!$service->integrator->hasOauthCredentials())
                    />
                    <label for="use_avatar" class="form-check-label">
                        {{ __('google-integrator::google.services.auth.use_avatar') }}
                    </label>
                    <div id="use_avatar_help"
                         class="form-text">{!! __('google-integrator::google.services.auth.use_avatar.description')!!}</div>
                </div>
                <div class="mb-2">{{ __('google-integrator::google.services.auth.autoconnect') }}</div>
                <div class="ms-3">
                    @foreach(\halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices::userServices() as $googleService)
                        <div class="col form-check form-switch mb-3">
                            <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="{{ $googleService->value }}"
                                    name="services[]"
                                    value="{{ $googleService->value }}"
                                    switch
                                    @checked(in_array($googleService->value, $service->data->services))
                                    @disabled(!$service->integrator->hasOauthCredentials())
                            />
                            <label for="{{ $googleService->value }}" class="form-check-label">
                                {{ $googleService->label() }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <button
                        type="submit"
                        class="col btn btn-primary"
                        @disabled(!$service->integrator->hasOauthCredentials())
                    >{{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('integrators.google.services.auth.service.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="card mb-3" x-data="{ expanded: false }">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">
                    <i class="fa-solid fa-chevron-right me-3 show-as-action" @click="expanded = true" x-show="!expanded"></i>
                    <i class="fa-solid fa-chevron-down me-3 show-as-action" @click="expanded = false" x-show="expanded"></i>
                    {{ __('google-integrator::google.service') }}
                </h4>
                @if($service->integrator->hasServiceAccountCredentials())
                    <span class="badge bg-success">{{ __('common.enabled') }}</span>
                @else
                    <span class="badge bg-danger">{{ __('common.disabled') }}</span>
                @endif
            </div>
            <div class="card-body" x-show="expanded">
                <div class="alert alert-info mb-3">{!!  __('google-integrator::google.service.description') !!}</div>
                <div class="mb-3">
                    <label for="service_account" class="form-label">
                        {{ __('google-integrator::google.service.file') }}
                        @if($vault->hasFile('google', 'service_account'))
                            <i class="fs-6 ms-2 text-success fa-solid fa-check-circle"></i>
                        @else
                            <i class="fs-6 ms-2 text-danger fa-solid fa-times"></i>
                        @endif
                    </label>
                    <input
                            class="form-control"
                            type="file"
                            id="service_account"
                            name="service_account"
                            aria-describedby="service_account_help"
                    />
                    <div id="service_account_help"
                         class="form-text">{!! __('google-integrator::google.service.file.description')!!}</div>
                </div>
            </div>
            <div class="card-footer" x-show="expanded">
                <div class="row">
                    <button type="submit" class="col btn btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </form>
@endsection