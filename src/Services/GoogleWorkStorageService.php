<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Integrators\IntegrationServiceInterface;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleWorkConnection;

class GoogleWorkStorageService extends LmsIntegrationService
{
	
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::WORK;
	}
	
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.work');
	}
	
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.work.description');
	}
	
	public static function getDefaultData(): array
	{
		return
			[
				'service_account' => null,
				'root' => null,
			];
	}
	
	public static function canConnectToPeople(): bool
	{
		return false;
	}
	
	public static function canConnectToSystem(): bool
	{
		return true;
	}
	
	public static function getPath(): string
	{
		return 'work';
	}

	public function canEnable(): bool
	{
		return true;
	}

	public function getConnectionClass(): string
	{
		return GoogleWorkConnection::class;
	}

	public function canConnect(?Person $person = null): bool
	{
		if($person) return false;
		$vault = app()->make(SecureVault::class);
		//2 things must be true for the system to use this connection:
		//1. The service account must be configured in the integrator settings and
		//2. The service_account field must be populated.
		return $vault->hasFile('google', 'service_account') && filter_var($this->data->service_account,
				FILTER_VALIDATE_EMAIL);
	}

	public function canRegister(?Person $person = null): bool
	{
		return false;
	}

	public function canConfigure(?Person $person = null): bool
	{
		return !$person;
	}

	public function registrationUrl(?Person $person = null): ?string
	{
		return null;
	}

	public function configurationUrl(?Person $person = null): ?string
	{
		if(!$person)
			return route('integrators.google.services.work');
		return null;
	}
}