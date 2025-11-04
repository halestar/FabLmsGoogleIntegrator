<?php

namespace halestar\FabLmsGoogleIntegrator\Services;

use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Integrators\IntegrationServiceInterface;
use App\Models\Integrations\LmsIntegrationService;
use App\Models\People\Person;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleAuthConnection;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleDocumentsConnection;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;

class GoogleDocumentsService extends LmsIntegrationService
{
	/**
	 * @inheritDoc
	 */
	public static function getServiceType(): IntegratorServiceTypes
	{
		return IntegratorServiceTypes::DOCUMENTS;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getServiceName(): string
	{
		return __('google-integrator::google.services.documents');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getServiceDescription(): string
	{
		return __('google-integrator::google.services.documents.description');
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
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getPath(): string
	{
		return "documents";
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
		$googleAuthService = $this->integrator->services()
		                                      ->ofType(IntegratorServiceTypes::AUTHENTICATION)
		                                      ->first();
		/** @var GoogleAuthConnection $googleAuthConnection */
		$googleAuthConnection = $googleAuthService->connect($person);
		return $googleAuthConnection && $googleAuthConnection->hasActiveToken() && $googleAuthConnection->hasScope(GoogleIntegrationServices::DOCUMENTS);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConnectionClass(): string
	{
		return GoogleDocumentsConnection::class;
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
		return '';
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
}