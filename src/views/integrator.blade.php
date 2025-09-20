@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedIntegrator' => $integrator])

@section('integrator-content')
    @inject('vault', 'App\Classes\Integrators\SecureVault')
    <div class="alert alert-warning mb-3">{!! __('integrators.vault.warning') !!}</div>
    <form action="{{ route('integrators.google.integrator.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <h4>{{ __('google-integrator::google.oauth') }}</h4>
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

        <h4>{{ __('google-integrator::google.service') }}</h4>
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
            <div id="service_account_help" class="form-text">{!! __('google-integrator::google.service.file.description')!!}</div>
        </div>
        <div class="row mt-3">
            <button type="submit" class="btn btn-primary">{{ __('integrators.update') }}</button>
        </div>
    </form>
@endsection