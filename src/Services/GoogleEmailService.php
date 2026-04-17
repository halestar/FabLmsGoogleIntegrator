<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleEmailConnection;

class GoogleEmailService extends LmsIntegrationService
{
	/**
	 * @inheritDoc
	 */
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::EMAIL;
	}

	/**
	 * @inheritDoc
	 */
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.email');
	}

	/**
	 * @inheritDoc
	 */
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.email.description');
	}

	/**
	 * @inheritDoc
	 */
	public static function getDefaultData(): array
	{
		return
			[
				'account' => null,
			];
	}

	/**
	 * @inheritDoc
	 */
	public static function canConnectToPeople(): bool
	{
		return false;
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
		return 'email';
	}

	/**
	 * @inheritDoc
	 */
	public function canEnable(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getConnectionClass(): string
	{
		return GoogleEmailConnection::class;
	}

	/**
	 * @inheritDoc
	 */
	public function canConnect(?Person $person = null): bool
	{
		return ($person == null) && $this->integrator->hasServiceAccountCredentials() && $this->hasConnection();
	}

	/**
	 * @inheritDoc
	 */
	public function canRegister(?Person $person = null): bool
	{
		return ($person == null);
	}

	/**
	 * @inheritDoc
	 */
	public function canConfigure(?Person $person = null): bool
	{
		return $person == null;
	}

	/**
	 * @inheritDoc
	 */
	public function registrationUrl(?Person $person = null): ?string
	{
		if($person) return null;
		return route('integrators.google.services.email');
	}

	/**
	 * @inheritDoc
	 */
	public function configurationUrl(?Person $person = null): ?string
	{
		if($person) return null;
		return route('integrators.google.services.email');
	}
}