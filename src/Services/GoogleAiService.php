<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\Local\LocalIntegrator;
use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\Integrator;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAiConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleSystemAiConnection;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Support\Facades\Http;

class GoogleAiService extends LmsIntegrationService
{
	public const GEMINI_BASE_URL = 	"https://generativelanguage.googleapis.com/v1beta";
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
		return true;
	}
	
	public static function getPath(): string
	{
		return 'ai';
	}
	
	public static function canBeConfigured(): bool
	{
		return true;
	}
	
	public function canConnect(Person $person): bool
	{
		return false;
	}
	
	public function getConnectionClass(): string
	{
		return GoogleAiConnection::class;
	}
	
	public function getSystemConnectionClass(): string
	{
		return GoogleAiConnection::class;
	}
	
	public function systemAutoconnect(): bool
	{
		return false;
	}
	
	public function configurationUrl(): string
	{
		return route(Integrator::INTEGRATOR_ACTION_PREFIX . GoogleIntegrator::getPath() . '.services.ai');
	}
	
	public function canSystemConnect(): bool
	{
		return $this->hasSystemConnection() != null;
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
	    return $this->canSystemConnect();
    }

	public function testConnection($apiKey): bool
	{
		try
		{
			$url = self::GEMINI_BASE_URL . "/models";
			$response = Http::get($url, ['key' => $apiKey]);
			return $response->successful();
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
}