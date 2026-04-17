<?php

namespace halestar\FabLmsGoogleIntegrator\Enums;

use App\Enums\IntegratorServiceTypes;
use App\Traits\EnumToArray;
use Google\Service\Classroom;
use Google\Service\Drive;
use Google\Service\Gmail;

enum GoogleIntegrationServices: string
{
	use EnumToArray;
	
	case AUTHENTICATION = 'auth';
	case DOCUMENTS = 'documents';
	case WORK = 'work';
	case CLASSROOM = 'classroom';
	case EMAIL = 'email';
	
	static function userServices(): array
	{
		return [self::DOCUMENTS, self::CLASSROOM];
	}
	
	static function systemServices(): array
	{
		return [self::WORK, self::CLASSROOM, self::EMAIL];
	}
	
	public function label(): string
	{
		return match ($this)
		{
			self::AUTHENTICATION => __('integrators.services.auth'),
			self::DOCUMENTS => __('google-integrator::google.services.documents'),
			self::WORK => __('google-integrator::google.services.work'),
			self::CLASSROOM => __('google-integrator::google.services.classroom'),
			self::EMAIL => __('google-integrator::google.services.email'),
		};
		
	}
	
	public function scopes(): array
	{
		return match ($this)
		{
			self::AUTHENTICATION => ['openid', 'email', 'profile'],
			self::DOCUMENTS => [Drive::DRIVE],
			self::WORK => [Drive::DRIVE],
			self::CLASSROOM =>
			[
				Classroom::CLASSROOM_ROSTERS,
				Classroom::CLASSROOM_PROFILE_EMAILS,
				Classroom::CLASSROOM_PROFILE_PHOTOS,
				Classroom::CLASSROOM_COURSES,
			],
			self::EMAIL => [Gmail::GMAIL_SEND],
		};
	}

	public function serviceType(): IntegratorServiceTypes
	{
		return match ($this)
		{
			self::AUTHENTICATION => IntegratorServiceTypes::AUTHENTICATION,
			self::DOCUMENTS => IntegratorServiceTypes::DOCUMENTS,
			self::WORK => IntegratorServiceTypes::WORK,
			self::CLASSROOM => IntegratorServiceTypes::CLASSES,
			self::EMAIL => IntegratorServiceTypes::EMAIL,
		};
	}
}