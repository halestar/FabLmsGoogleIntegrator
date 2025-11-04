@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedService' => $service])

@section('integrator-content')
    @inject('vault', 'App\Classes\Integrators\SecureVault')
    <div class="alert alert-warning mb-3">{!! __('integrators.vault.warning') !!}</div>
    <form action="{{ route('integrators.google.services.system.ai.update') }}" method="post"
          x-data="{ users_ai: {{ $service->data->allow_user_ai? 'true': 'false' }} }">
        @csrf
        @method('PATCH')
        <h4>{{ __('google-integrator::google.services.ai.system.settings') }}</h4>
        <div class="mb-3">
            <label for="gemini_api" class="form-label">
                {{ __('google-integrator::google.services.ai.system.gemini_api') }}

                @if($vault->hasKey('google', 'gemini_api'))
                    <i class="fs-6 ms-2 text-success fa-solid fa-check-circle"></i>
                @else
                    <i class="fs-6 ms-2 text-danger fa-solid fa-times"></i>
                @endif
            </label>
            <input
                    type="text"
                    class="form-control"
                    id="gemini_api"
                    name="gemini_api"
                    aria-describedby="gemini_api_help"
            />
            <div id="gemini_api_help"
                 class="form-text">{!! __('google-integrator::google.services.ai.system.gemini_api.description')!!}</div>
        </div>

        <div class="form-check form-switch mb-3">
            <input
                    class="form-check-input"
                    x-model="users_ai"
                    type="checkbox"
                    role="switch"
                    id="allow_user_ai"
                    name="allow_user_ai"
                    value="1"
                    aria-describedby="allow_user_ai_help"
                    switch
            />
            <label for="allow_user_ai" class="form-check-label">
                {{ __('google-integrator::google.services.ai.allow_user_ai') }}
            </label>
            <div id="allow_user_ai_help"
                 class="form-text">{!! __('google-integrator::google.services.ai.allow_user_ai.description')!!}</div>
        </div>

        <div class="form-check form-switch mb-3">
            <input
                    class="form-check-input"
                    @checked($service->data->allow_user_system_ai)
                    type="radio"
                    role="switch"
                    id="allow_user_system_ai"
                    name="user_allow_ai"
                    value="allow_user_system_ai"
                    aria-describedby="allow_user_system_ai_help"
                    x-bind:disabled="!users_ai"
                    switch
            />
            <label for="allow_user_system_ai" class="form-check-label">
                {{ __('google-integrator::google.services.ai.allow_user_system_ai') }}
            </label>
            <div id="allow_user_system_ai_help"
                 class="form-text">{!! __('google-integrator::google.services.ai.allow_user_system_ai.description')!!}</div>
        </div>

        <div class="form-check form-switch mb-3">
            <input
                    class="form-check-input"
                    @checked($service->data->allow_user_own_ai)
                    type="radio"
                    role="switch"
                    id="allow_user_own_ai"
                    name="user_allow_ai"
                    value="allow_user_own_ai"
                    aria-describedby="allow_user_own_ai_help"
                    x-bind:disabled="!users_ai"
                    switch
            />
            <label for="allow_user_own_ai" class="form-check-label">
                {{ __('google-integrator::google.services.ai.allow_user_own_ai') }}
            </label>
            <div id="allow_user_own_ai_help"
                 class="form-text">{!! __('google-integrator::google.services.ai.allow_user_own_ai.description')!!}</div>
        </div>

        @if($connection)
            <div class="alert alert-success mb-3">
                {{ __('google-integrator::google.services.work.connected') }}

            </div>
        @else
            <div class="alert alert-danger mb-3">
                {{ __('google-integrator::google.services.work.disconnected') }}
            </div>
        @endif

        <div class="row mt-3">
            <button type="submit" class="btn btn-primary">{{ __('integrators.service.update') }}</button>
        </div>
    </form>
@endsection