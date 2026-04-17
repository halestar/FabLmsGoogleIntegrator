@extends('layouts.integrations', ['breadcrumb' => $breadcrumb, 'selectedService' => $service])

@section('integrator-content')
    @if(!$hasServiceAccount)
        <div class="alert alert-danger mb-3">
            {{ __('google-integrator::google.service.required') }}
        </div>
    @else
        <form action="{{ route('integrators.google.services.work.update') }}" method="POST">
            @csrf
            @method('PATCH')
            <h4 class="mb-3">{{ __('google-integrator::google.services.work.settings') }}</h4>
            <div class="mb-3">
                <label for="service_account"
                       class="form-label">{{ __('google-integrator::google.services.work.service_account') }}</label>
                <input
                        type="email"
                        class="form-control @error('service_account') is-invalid @enderror"
                        id="service_account"
                        name="service_account"
                        value="{{ $service->data->service_account }}"
                        aria-describedby="service_account_help"
                />
                <x-utilities.error-display key="service_account">{{ $errors->first('service_account') }}</x-utilities.error-display>
                <div id="service_account_help"
                     class="form-text">{!! __('google-integrator::google.services.work.service_account.description') !!}</div>
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

            <div class="row">
                <button type="submit" class="btn btn-primary">{{ __('integrators.service.update') }}</button>
            </div>
        </form>
    @endif
@endsection