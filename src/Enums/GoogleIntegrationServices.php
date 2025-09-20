<?php
namespace halestar\FabLmsGoogleIntegrator\Enums;
use App\Traits\EnumToArray;
use Google\Service\Drive;

enum GoogleIntegrationServices: string
{
	use EnumToArray;
	case AUTHENTICATION = 'auth';
	case DOCUMENTS = 'documents';
	case WORK = 'work';
	
	public function label(): string
	{
		return match($this)
		{
			self::AUTHENTICATION => __('integrators.services.auth'),
			self::DOCUMENTS => __('google-integrator::google.services.documents'),
			self::WORK => __('google-integrator::google.services.work'),
		};
		
	}
	
	public function scopes(): array
	{
		return match($this)
		{
			self::AUTHENTICATION => ['openid', 'email', 'profile'],
			self::DOCUMENTS => [Drive::DRIVE],
			self::WORK => [Drive::DRIVE],
		};
	}
	
	static function userServices(): array
	{
		return [self::DOCUMENTS];
	}
	static function systemServices(): array
	{
		return [self::WORK];
	}
}