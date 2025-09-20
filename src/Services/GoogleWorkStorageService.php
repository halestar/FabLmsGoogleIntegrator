<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Integrators\IntegrationServiceInterface;
use App\Models\Integrations\IntegrationService;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleWorkConnection;

class GoogleWorkStorageService extends LmsIntegrationService
{
	
	public function canConnect(Person $person): bool
	{
		return false;
	}
	
	public function getConnectionClass(): string
	{
		return '';
	}
	
	public function getSystemConnectionClass(): string
	{
		return GoogleWorkConnection::class;
	}
	
	public function systemAutoconnect(): bool
	{
		return false;
	}
	
	public function configurationUrl(): string
	{
		return route('integrators.google.services.work');
	}
	
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
	
	public static function canBeConfigured(): bool
	{
		return true;
	}
	
	public function canSystemConnect(): bool
	{
		$vault = app()->make(SecureVault::class);
		//2 things must be true for the system to use this connection:
		//1. The service account must be configured in the intetegrator settings and
		//2. The service_account field must be populated.
		return $vault->hasFile('google', 'service_account') &&  filter_var($this->data->service_account, FILTER_VALIDATE_EMAIL);
	}
	
	public function canRegister(): bool
	{
		return false;
	}
	
	public function registrationUrl(): string
	{
		return '';
	}
}