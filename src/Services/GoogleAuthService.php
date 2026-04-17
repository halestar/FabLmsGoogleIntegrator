<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAuthConnection;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthService extends LmsIntegrationService
{

	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::AUTHENTICATION;
	}

	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.auth');
	}

	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.auth.description');
	}

	public static function getDefaultData(): array
	{
		return
			[
				'use_avatar' => false,
				'allow_user_connection' => true,
				'services' =>
					[
						IntegratorServiceTypes::DOCUMENTS,
					],
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
		return "auth";
	}

	public function canEnable(): bool
	{
		return true;
	}

	public function getConnectionClass(): string
	{
		return GoogleAuthConnection::class;
	}

	public function canConnect(Person $person = null): bool
	{
		return ($person && $this->hasConnection($person));
	}

	public function canRegister(?Person $person = null): bool
	{
		if(!$person) return false;
		return $this->data->allow_user_connection;
	}

	public function canConfigure(?Person $person = null): bool
	{
		return ($person == null);
	}

	public function registrationUrl(?Person $person = null): ?string
	{
		if(!$person)
			return null;
		//first, does the user have a valid token?
		$scopes = [];
		foreach($this->data->services as $service)
			$scopes = array_merge($scopes, GoogleIntegrationServices::from($service)->scopes());
		session()->put('google_auth_registering', true);
		return Socialite::driver('google')
			->with(
				[
					'login_hint' => $person->system_email,
					'prompt' => 'consent',
					'access_type' => 'offline',
				])
			->scopes($scopes)
			->redirect()
			->getTargetUrl();
	}
	
	/**
	 * @inheritDoc
	 */
	public function configurationUrl(?Person $person = null): ?string
	{
		if(!$person)
			return route('integrators.google.services.auth');
		return null;
	}
}