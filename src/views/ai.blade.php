@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedService' => $service])

@section('integrator-content')
    <h4 class="mt-5">
        {{ __('google-integrator::google.services.ai.system.settings') }}
        @if($connection)
            <span class="badge text-bg-success ms-2">{{ __('integrators.local.ai.verified') }}</span>
        @else
            <span class="badge text-bg-danger ms-2">{{ __('integrators.local.ai.verified.no') }}</span>
        @endif
    </h4>
    <form action="{{ route('integrators.google.services.ai.update') }}" method="POST">
        @csrf
        @method('PUT')
        @error('gemini_api')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
        @enderror
        <div class="input-group mb-3">
            <span id="geminir-api-label" class="input-group-text">
                {{ __('google-integrator::google.services.ai.system.gemini_api') }}
            </span>
            <input
                type="text"
                @if($connection)
                    placeholder="{{ __('google-integrator::google.services.ai.system.gemini_api.hidden') }}"
                @else
                    placeholder="{{ __('google-integrator::google.services.ai.system.gemini_api') }}"
                @endif
                class="form-control"
                id="gemini_api"
                name="gemini_api"
                aria-describedby="gemini_api_help"
            />
            <button type="submit" class="btn btn-primary">{{ __('integrators.local.ai.connect') }}</button>
        </div>
        <div id="gemini_api_help"
             class="form-text"
        >{!! __('google-integrator::google.services.ai.system.gemini_api.description')!!}</div>
    </form>
    @if($connection)
        <livewire:ai.llm-manager :connection_id="$connection->id" />
    @endif
@endsection