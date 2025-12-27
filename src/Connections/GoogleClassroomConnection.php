<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Casts\Utilities\AsJsonData;
use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\Connections\ClassesConnection;
use App\Models\Integrations\IntegrationConnection;
use App\Models\SubjectMatter\ClassSession;
use App\Models\SubjectMatter\SchoolClass;
use Google\Client as GoogleClient;
use Google_Service_Classroom;
use Google_Service_Exception;
use halestar\FabLmsGoogleIntegrator\Casts\ClassroomLink;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Support\Facades\Log;
use Google\Service\Classroom\Course as GoogleCourse;

class GoogleClassroomConnection extends ClassesConnection
{
	protected GoogleClient $client;
	protected ?Google_Service_Classroom $classroom = null;

	protected static function booted(): void
	{
		static::retrieved(function(IntegrationConnection $connection)
		{
			$vault = app()->make(SecureVault::class);
			$connection->client = new GoogleClient();
			$connection->client->setClientId($vault->retrieve('google', 'client_id'));
			$connection->client->setClientSecret($vault->retrieve('google', 'client_secret'));;
			//find the google auth service and connect to the user.
			/** @var GoogleAuthConnection $authConnection */
			$authConnection = GoogleIntegrator::getService(IntegratorServiceTypes::AUTHENTICATION)->connect($connection->person);
			if($authConnection && $authConnection->hasActiveToken())
			{
				$connection->client->setAccessToken($authConnection->data->oauth_token);
				$connection->classroom = new Google_Service_Classroom($connection->client);
			}
		});
	}

	protected function casts(): array
	{
		$casts = parent::casts();
		$casts['data'] = ClassroomLink::class;
		return $casts;
	}

	public function manageClass(ClassSession $classSession): mixed
	{
		return "";
	}

	public static function getSystemInstanceDefault(): array
	{
		return [];
	}

	public static function getInstanceDefault(): array
	{
		return [];
	}

	public function hasPreferences(): bool
	{
		return true;
	}

	public function preferencesRoute(SchoolClass $schoolClass): string
	{
		return route('integrators.google.services.classroom.preferences', ['schoolClass' => $schoolClass->id]);
	}

	public function getClassLink(SchoolClass $schoolClass): ?ClassroomLink
	{
		return $this->data[$schoolClass->id] ?? null;
	}

	public function setClassLink(ClassroomLink $link): void
	{
		$data = $this->data;
		$data[$link->class_id] = $link;
		$this->data = $data;
	}

	public function listCourses()
	{
		try
		{
			$results = $this->classroom->courses->listCourses(['teacherId' => 'me']);
			$courses = $results->getCourses();
		}
		catch(Google_Service_Exception $e)
		{
			Log::error("failed to list the courses fot the user " . $this->person->email . ": " . $e->getMessage());
			$courses = [];
		}
		return $courses;
	}

	public function createCourse(ClassSession $classSession): void
	{
		$gCourse = new GoogleCourse();
		$gCourse->setName($classSession->name . "(" . $classSession->term->label . ")");
		$gCourse->setSection($classSession->scheduleString());
		$gCourse->room = $classSession->room->name;
		$gCourse->setOwnerId("me");
		$gCourse->setCourseState("ACTIVE");
		try
		{
			$response = $this->classroom->courses->create($gCourse);
		}
		catch(Google_Service_Exception $e)
		{
			Log::error("failed to list the courses for the user " . $this->person->email . ": " . $e->getMessage());
			$response = null;
		}
		$link = $this->getClassLink($classSession->schoolClass)?? ClassroomLink::defaultInstance($classSession->schoolClass);
		$link->sessions[$classSession->id] = $response->id;
		$this->setClassLink($link);
		$this->save();
	}

	public function syncCourseInformation(SchoolClass $schoolClass)
	{
		$classLink = $this->getClassLink($schoolClass);
		if($classLink && $classLink->sync_basic)
		{
			foreach($schoolClass->sessions as $session)
			{
				if(!$classLink->sessions[$session->id])
					continue;
				try
				{
					//get the course for this session
					/** @var GoogleCourse $gCourse */
					$gCourse = $this->classroom->courses->get($classLink->sessions[$session->id]);
					if($gCourse)
					{
						$gCourse->setName($session->name . "(" . $session->term->label . ")");
						$gCourse->setSection($session->scheduleString());
						$gCourse->setDescriptionHeading($schoolClass->course->course_name);
						$gCourse->setDescription($schoolClass->course->description);
						$gCourse->setRoom($session->room->name);
						//and we update it.
						$this->classroom->courses->update($classLink->sessions[$session->id], $gCourse);
					}
				}
				catch(Google_Service_Exception $e)
				{
					Log::error("failed to update the course for session " . $session->id . ": " . $e->getMessage());
				}
			}
		}

	}

	public function syncStudentInformation(SchoolClass $schoolClass)
	{

	}

	public function syncAssignmentInformation(SchoolClass $schoolClass)
	{

	}
}