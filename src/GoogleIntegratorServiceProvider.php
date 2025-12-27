<?php

namespace halestar\FabLmsGoogleIntegrator;

use halestar\FabLmsGoogleIntegrator\Livewire\ClassPreferences;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class GoogleIntegratorServiceProvider extends ServiceProvider
{
	
	public function boot(): void
	{
		$this->loadViewsFrom(__DIR__ . '/views', 'google-integrator');
		$this->loadTranslationsFrom(__DIR__ . '/lang', 'google-integrator');
		$this->publishes([
			__DIR__ . '/public' => public_path('vendor/google-integrator'),
		], 'public');
		Livewire::component('google-integrator.class-preferences', ClassPreferences::class);
	}
	
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__ . '/config/services.php', 'services');
	}
}