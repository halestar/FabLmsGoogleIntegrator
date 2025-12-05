<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAiConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleSystemAiConnection;

class GoogleAiService extends LmsIntegrationService
{
	
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
			];
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
		return 'system-ai';
	}
	
	public static function canBeConfigured(): bool
	{
		return false;
	}
	
	public function canConnect(Person $person): bool
	{
		$vault = app()->make(SecureVault::class);
		if($vault->hasKey('google', 'gemini_api'))
			return true;
		return ($this->getServiceConnection($person)?->data->key);
	}
	
	public function getConnectionClass(): string
	{
		return GoogleAiConnection::class;
	}
	
	public function getSystemConnectionClass(): string
	{
		return '';
	}
	
	public function systemAutoconnect(): bool
	{
		return false;
	}
	
	public function configurationUrl(): string
	{
		return '';
	}
	
	public function canSystemConnect(): bool
	{
		return false;
	}
	
	public function canRegister(): bool
	{
		return true;
	}
	
	public function registrationUrl(): string
	{
		return route('integrators.google.services.ai.register');
	}

    public function canEnable(): bool
    {
        $vault = app(SecureVault::class);
        return $vault->hasKey('google', 'gemini_api');
    }
}