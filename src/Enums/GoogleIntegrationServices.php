<?php

namespace halestar\FabLmsGoogleIntegrator\Enums;

use App\Traits\EnumToArray;
use Google\Service\Classroom;
use Google\Service\Drive;

enum GoogleIntegrationServices: string
{
	use EnumToArray;
	
	case AUTHENTICATION = 'auth';
	case DOCUMENTS = 'documents';
	case WORK = 'work';
	case CLASSROOM = 'classroom';
	
	static function userServices(): array
	{
		return [self::DOCUMENTS, self::CLASSROOM];
	}
	
	static function systemServices(): array
	{
		return [self::WORK, self::CLASSROOM];
	}
	
	public function label(): string
	{
		return match ($this)
		{
			self::AUTHENTICATION => __('integrators.services.auth'),
			self::DOCUMENTS => __('google-integrator::google.services.documents'),
			self::WORK => __('google-integrator::google.services.work'),
			self::CLASSROOM => __('google-integrator::google.services.classroom'),
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
		};
	}
}