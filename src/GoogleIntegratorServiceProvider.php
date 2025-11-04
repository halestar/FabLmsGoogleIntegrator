<?php

namespace halestar\FabLmsGoogleIntegrator;

use Illuminate\Support\ServiceProvider;

class GoogleIntegratorServiceProvider extends ServiceProvider
{
	
	public function boot(): void
	{
		$this->loadViewsFrom(__DIR__ . '/views', 'google-integrator');
		$this->loadTranslationsFrom(__DIR__ . '/lang', 'google-integrator');
		$this->publishes([
			__DIR__ . '/public' => public_path('vendor/google-integrator'),
		], 'public');
	}
	
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__ . '/config/services.php', 'services');
	}
}