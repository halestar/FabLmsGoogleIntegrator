@extends('layouts.app', ['breadcrumb' => $breadcrumb])

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h4 class="mt-5">
                    {{ __('google-integrator::google.services.ai.system.settings') }}
                    @if($connection)
                        <span class="badge text-bg-success ms-2">{{ __('integrators.local.ai.verified') }}</span>
                    @else
                        <span class="badge text-bg-danger ms-2">{{ __('integrators.local.ai.verified.no') }}</span>
                    @endif
                </h4>
                <div class="alert alert-warning mb-3">{!! __('integrators.vault.user.warning') !!}</div>
                <form action="{{ route('integrators.google.services.ai.register.update') }}" method="post">
                    @csrf
                    @method('PATCH')
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
            </div>
        </div>
        @if($connection)
            <livewire:ai.llm-manager :connection_id="$connection->id" />
        @endif
    </div>
@endsection