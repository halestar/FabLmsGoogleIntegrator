<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAuthConnection;

class GoogleAuthService extends LmsIntegrationService
{
	
	/**
	 * @inheritDoc
	 */
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::AUTHENTICATION;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.auth');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.auth.description');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDefaultData(): array
	{
		return
			[
				'use_avatar' => false,
				'autoconnect' => [IntegratorServiceTypes::DOCUMENTS],
			];
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
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getPath(): string
	{
		return "auth";
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
	public function canConnect(Person $person): bool
	{
		return $this->integrator->verifySettings();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConnectionClass(): string
	{
		return GoogleAuthConnection::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSystemConnectionClass(): string
	{
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function systemAutoconnect(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function configurationUrl(): string
	{
		return route('integrators.google.services.auth');
	}
	
	public function canSystemConnect(): bool
	{
		return false;
	}
	
	public function canRegister(): bool
	{
		return false;
	}
	
	public function registrationUrl(): string
	{
		return '';
	}

    public function canEnable(): bool
    {
        $vault = app(SecureVault::class);
        return $vault->hasKey('google', 'client_id') &&
            $vault->hasKey('google', 'client_secret') &&
            $vault->hasKey('google', 'redirect');
    }
}