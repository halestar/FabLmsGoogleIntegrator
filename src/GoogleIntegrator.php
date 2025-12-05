<?php

namespace halestar\FabLmsGoogleIntegrator;

use App\Classes\AI\AiSchema;
use App\Classes\Integrators\IntegrationsManager;
use App\Classes\Integrators\SecureVault;
use App\Enums\AiSchemaType;
use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Integrators\IntegratorInterface;
use App\Models\Integrations\LmsIntegrator;
use App\Models\People\Person;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use halestar\FabLmsGoogleIntegrator\Controllers\GoogleIntegratorController;
use halestar\FabLmsGoogleIntegrator\Services\GoogleAiService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleAuthService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleDocumentsService;
use halestar\FabLmsGoogleIntegrator\Services\GoogleWorkStorageService;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class GoogleIntegrator extends LmsIntegrator
{
	/**
	 * @inheritDoc
	 */
	public static function integratorName(): string
	{
		return __('google-integrator::google.name');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function integratorDescription(): string
	{
		return __('google-integrator::google.description');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function defaultData(): array
	{
		return
			[
			
			];
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getVersion(): string
	{
		return "0.1";
	}
	
	/**
	 * @inheritDoc
	 */
	public static function canConnectToPeople(): bool
	{
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function canConnectToSystem(): bool
	{
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getPath(): string
	{
		return "google";
	}
	
	/**
	 * @inheritDoc
	 */
	public static function canBeConfigured(): bool
	{
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function registerServices(IntegrationsManager $manager, bool $overwrite = false): void
	{
		$manager->registerService($this, GoogleAuthService::class, $overwrite);
		$manager->registerService($this, GoogleDocumentsService::class, $overwrite);
		$manager->registerService($this, GoogleWorkStorageService::class, $overwrite);
		$manager->registerService($this, GoogleAiService::class, $overwrite);
	}
	
	/**
	 * @inheritDoc
	 */
	public function isOutdated(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasService(IntegratorServiceTypes $type): bool
	{
		return ($type == IntegratorServiceTypes::AUTHENTICATION ||
			$type == IntegratorServiceTypes::WORK ||
			$type == IntegratorServiceTypes::DOCUMENTS ||
			$type == IntegratorServiceTypes::AI);
	}
	
	/**
	 * @inheritDoc
	 */
	public function configurationUrl(): string
	{
		return route('integrators.google.integrator');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getImageUrl(): string
	{
		return asset('vendor/google-integrator/logo.svg');
	}
	
	/**
	 * @inheritDoc
	 */
	public function publishRoutes(): void
	{
		Route::get('/', [GoogleIntegratorController::class, 'integrator'])
		     ->name('integrator');
		Route::patch('/', [GoogleIntegratorController::class, 'update'])
		     ->name('integrator.update');
		Route::get('services/auth', [GoogleIntegratorController::class, 'auth'])
		     ->name('services.auth');
		Route::patch('services/auth', [GoogleIntegratorController::class, 'authUpdate'])
		     ->name('services.auth.update');
		Route::get('services/work', [GoogleIntegratorController::class, 'work'])
		     ->name('services.work');
		Route::patch('services/work', [GoogleIntegratorController::class, 'workUpdate'])
		     ->name('services.work.update');
		Route::get('services/register/ai', [GoogleIntegratorController::class, 'registerAi'])
		     ->name('services.ai.register');
		Route::patch('services/register/ai', [GoogleIntegratorController::class, 'updateAiRegistration'])
		     ->name('services.ai.register.update');
	}
	
	public function isIntegrated(Person $person): bool
	{
		$connection = $this->services()
		                   ->ofType(IntegratorServiceTypes::AUTHENTICATION)
		                   ->first()
		                   ?->connect($person);
		return ($connection && $connection->hasActiveToken());
	}
	
	public function integrationUrl(Person $person): string
	{
		//get the auth service
		$authService = $this->services()
		                    ->ofType(IntegratorServiceTypes::AUTHENTICATION)
		                    ->first();
		$connection = $authService->connect($person);
		return $connection->redirect()
		                  ->getTargetUrl();
	}
	
	public function removeIntegration(Person $person): void
	{
		//we will forget the connection for each service we have a connection for.
		foreach($this->services()
		             ->personal()
		             ->get() as $service)
			$person->removeIntegrationService($service);
	}
	
	protected function canIntegrate(Person $person): bool
	{
		return $this->verifySettings();
	}
	
	public function verifySettings()
	{
		$vault = app()->make(SecureVault::class);
		return $vault->hasKey('google', 'client_id') && $vault->hasKey('google', 'client_secret')
			&& $vault->hasKey('google', 'redirect');
	}
}