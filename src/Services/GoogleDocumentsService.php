<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Integrators\IntegrationServiceInterface;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAuthConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleDocumentsConnection;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;

class GoogleDocumentsService extends LmsIntegrationService
{
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::DOCUMENTS;
	}

	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.documents');
	}

	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.documents.description');
	}

	public static function getDefaultData(): array
	{
		return [];
	}
	
	public static function canConnectToPeople(): bool
	{
		return true;
	}
	
	public static function canConnectToSystem(): bool
	{
		return false;
	}

	public static function getPath(): string
	{
		return "documents";
	}

	public function canEnable(): bool
	{
		return $this->integrator->hasOauthCredentials();
	}

	public function getConnectionClass(): string
	{
		return GoogleDocumentsConnection::class;
	}

	public function canConnect(?Person $person = null): bool
	{
		if(!$person) return false;
		//2 things must be true to be able to connect to the drive service:
		//1. The use must have an authenticated google connection
		//2. The scope must be enabled for the user.
		$googleAuthService = $this->integrator->services()
			->ofType(IntegratorServiceTypes::AUTHENTICATION)
			->first();
		/** @var GoogleAuthConnection $googleAuthConnection */
		$googleAuthConnection = $googleAuthService->connect($person);
		return $googleAuthConnection && $googleAuthConnection->hasActiveToken() && $googleAuthConnection->hasScope(GoogleIntegrationServices::DOCUMENTS);
	}

	public function canRegister(?Person $person = null): bool
	{
		return false;
	}

	public function canConfigure(?Person $person = null): bool
	{
		return false;
	}

	public function registrationUrl(?Person $person = null): ?string
	{
		return null;
	}

	public function configurationUrl(?Person $person = null): ?string
	{
		return null;
	}
}