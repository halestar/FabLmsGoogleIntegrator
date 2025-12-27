<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAuthConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleClassroomConnection;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;

class GoogleClassroomService extends LmsIntegrationService
{

	/**
	 * @inheritDoc
	 */
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::CLASSES;
	}

	/**
	 * @inheritDoc
	 */
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.classroom');
	}

	/**
	 * @inheritDoc
	 */
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.classroom.description');
	}

	/**
	 * @inheritDoc
	 */
	public static function getDefaultData(): array
	{
		return [];
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
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public static function getPath(): string
	{
		return 'classroom';
	}

	/**
	 * @inheritDoc
	 */
	public static function canBeConfigured(): bool
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function canConnect(Person $person): bool
	{
		//2 things must be true to be able to connect to the drive service:
		//1. The use must have an authenticated google connection
		//2. The scope must be enabled for the user.
		$googleAuthService = GoogleIntegrator::getService(IntegratorServiceTypes::AUTHENTICATION);
		/** @var GoogleAuthConnection $googleAuthConnection */
		$googleAuthConnection = $googleAuthService->connect($person);
		return $googleAuthConnection && $googleAuthConnection->hasActiveToken() &&
			$googleAuthConnection->hasScope(GoogleIntegrationServices::CLASSROOM);
	}

	/**
	 * @inheritDoc
	 */
	public function getConnectionClass(): string
	{
		return GoogleClassroomConnection::class;
	}

	/**
	 * @inheritDoc
	 */
	public function canSystemConnect(): bool
	{
		//system can only connect if a service account is configured
		$vault = app(SecureVault::class);
		return $vault->hasFile('google', 'service_account');
	}

	/**
	 * @inheritDoc
	 */
	public function getSystemConnectionClass(): string
	{
		return GoogleClassroomConnection::class;
	}

	/**
	 * @inheritDoc
	 */
	public function canRegister(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function registrationUrl(): string
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function systemAutoconnect(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function configurationUrl(): string
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function canEnable(): bool
	{
		$vault = app(SecureVault::class);
		return ($vault->hasKey('google', 'client_id') &&
			$vault->hasKey('google', 'client_secret') &&
			$vault->hasKey('google', 'redirect')) ||
			$vault->hasFile('google', 'service_account');
	}
}