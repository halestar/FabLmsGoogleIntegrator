<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\IntegrationService;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAiConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleSystemAiConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleWorkConnection;

class GoogleAiService extends LmsIntegrationService
{
	
	public function canConnect(Person $person): bool
	{
		if(!$this->data->allow_user_ai)
			return false;
		$vault = app()->make(SecureVault::class);
		if($this->data->allow_user_system_ai && $vault->hasKey('google', 'gemini_api'))
			return true;
		return ($this->data->allow_user_own_ai && $this->getServiceConnection($person)?->data->key);
	}
	
	public function getConnectionClass(): string
	{
		return GoogleAiConnection::class;
	}
	
	public function getSystemConnectionClass(): string
	{
		return GoogleSystemAiConnection::class;
	}
	
	public function systemAutoconnect(): bool
	{
		return false;
	}
	
	public function configurationUrl(): string
	{
		return route('integrators.google.services.system.ai');
	}
	
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::AI;
	}
	
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.ai');
	}
	
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.ai.description');
	}
	
	public static function getDefaultData(): array
	{
		return
			[
				'allow_user_system_ai' => false,
				'allow_user_ai' => false,
				'allow_user_own_ai' => false,
			];
	}
	
	public static function canConnectToPeople(): bool
	{
		return true;
	}
	
	public static function canConnectToSystem(): bool
	{
		return true;
	}
	
	public static function getPath(): string
	{
		return 'system-ai';
	}
	
	public static function canBeConfigured(): bool
	{
		return true;
	}
	
	public function canSystemConnect(): bool
	{
		$vault = app()->make(SecureVault::class);
		//We can only connect if we have a valid API key
		return $vault->hasKey('google', 'gemini_api');
	}
	
	public function canRegister(): bool
	{
		return $this->data->allow_user_ai && $this->data->allow_user_own_ai;
	}
	
	public function registrationUrl(): string
	{
		return route('integrators.google.services.ai.register');
	}
}