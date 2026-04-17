<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\Local\LocalIntegrator;
use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\Integrator;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAiConnection;
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
				'allow_user_connections' => true,
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

	public function canEnable(): bool
	{
		return true;
	}

	public function getConnectionClass(): string
	{
		return GoogleAiConnection::class;
	}

	public function canConnect(?Person $person = null): bool
	{
		return $this->hasConnection($person);
	}

	public function canRegister(?Person $person = null): bool
	{
		return !$person || $this->data->allow_user_connections;
	}

	public function canConfigure(?Person $person = null): bool
	{
		return !$person || $this->data->allow_user_connections;
	}

	public function registrationUrl(?Person $person = null): string
	{
		return route('integrators.google.services.ai.register');
	}
	
	public function configurationUrl(?Person $person = null): string
	{
		return route(Integrator::INTEGRATOR_ACTION_PREFIX . GoogleIntegrator::getPath() . '.services.ai');
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