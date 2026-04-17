<?php

namespace halestar\FabLmsGoogleIntegrator;

use App\Classes\Integrators\IntegrationsManager;
use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrator;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Controllers\GoogleIntegratorController;
use halestar\FabLmsGoogleIntegrator\Services\GoogleAiService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleAuthService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleClassroomService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleDocumentsService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleEmailService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleWorkStorageService;
use Illuminate\Support\Facades\Route;

class GoogleIntegrator extends LmsIntegrator
{

	public static function getPath(): string
	{
		return "google";
	}

	public static function integratorName(): string
	{
		return __('google-integrator::google.name');
	}
	
	public static function integratorDescription(): string
	{
		return __('google-integrator::google.description');
	}
	
	public static function defaultData(): array
	{
		return [];
	}

	public static function getVersion(): string
	{
		return "0.3";
	}

	public static function canConnectToPeople(): bool
	{
		return true;
	}

	public static function canConnectToSystem(): bool
	{
		return true;
	}

	public static function canBeConfigured(): bool
	{
		return false;
	}

	public function registerServices(IntegrationsManager $manager, bool $overwrite = false): void
	{
		$manager->registerService($this, GoogleAuthService::class, $overwrite);
		$manager->registerService($this, GoogleDocumentsService::class, $overwrite);
		$manager->registerService($this, GoogleWorkStorageService::class, $overwrite);
		$manager->registerService($this, GoogleAiService::class, $overwrite);
		$manager->registerService($this, GoogleEmailService::class, $overwrite);
		//$manager->registerService($this, GoogleClassroomService::class, $overwrite);
	}

	public function isOutdated(): bool
	{
		return false;
	}

	public function hasService(IntegratorServiceTypes $type): bool
	{
		return ($type == IntegratorServiceTypes::AUTHENTICATION ||
			$type == IntegratorServiceTypes::WORK ||
			$type == IntegratorServiceTypes::DOCUMENTS ||
			$type == IntegratorServiceTypes::AI);
			// $type == IntegratorServiceTypes::CLASSES);
	}

	public function getImageUrl(): string
	{
		return asset('vendor/google-integrator/logo.svg');
	}

	public function publishRoutes(): void
	{
		Route::controller(GoogleIntegratorController::class)
			->group(function ()
			{
				//authentication settings
				Route::get('services/auth', 'googleAuth')
					->name('services.auth');
				Route::patch('services/oauth', 'oauthUpdate')
					->name('services.oauth.update');
				Route::patch('services/auth', 'authUpdate')
					->name('services.auth.update');
				Route::patch('services/auth/service', 'authServiceUpdate')
					->name('services.auth.service.update');

				//Work document storage settings
				Route::get('services/work', 'work')
					->name('services.work');
				Route::patch('services/work', 'workUpdate')
					->name('services.work.update');

				//AI system settings.
				Route::get('services/ai', 'ai')
					->name('services.ai');
				Route::put('services/ai', 'updateAi')
					->name('services.ai.update');
				//AI personal settings.
				Route::get('services/register/ai', 'registerAi')
					->name('services.ai.register')
					->withoutMiddleware(['can:settings.integrators']);
				Route::patch('services/register/ai', 'updateAiRegistration')
					->name('services.ai.register.update')
					->withoutMiddleware(['can:settings.integrators']);

				//Email settings
				Route::get('services/email', 'email')
					->name('services.email');
				Route::patch('services/email', 'emailUpdate')
					->name('services.email.update');
			});
	}

	public function hasOauthCredentials(): bool
	{
		$vault = app()->make(SecureVault::class);
		return $vault->hasKey('google', 'client_id') && $vault->hasKey('google', 'client_secret')
		       && $vault->hasKey('google', 'redirect');
	}

	public function hasServiceAccountCredentials(): bool
	{
		$vault = app()->make(SecureVault::class);
		return $vault->hasFile('google', 'service_account');
	}
}