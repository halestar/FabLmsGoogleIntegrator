@extends('layouts.app', ['breadcrumb' => $breadcrumb])

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="alert alert-warning mb-3">{!! __('integrators.vault.user.warning') !!}</div>
                <form action="{{ route('integrators.google.services.ai.register.update') }}" method="post">
                    @csrf
                    @method('PATCH')
                    <h4>{{ __('google-integrator::google.services.ai.system.gemini_api') }}</h4>
                    <div class="alert alert-info mb-3">{!!  __('google-integrator::google.services.ai.system.gemini_api.description') !!}</div>
                    <div class="mb-3">
                        <input
                                type="text"
                                class="form-control"
                                id="client_secret"
                                name="client_secret"
                                aria-describedby="client_secret_help"
                        />
                    </div>
                    <div class="row mt-3">
                        <button type="submit" class="btn btn-primary">{{ __('integrators.update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection