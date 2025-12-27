<?php

namespace halestar\FabLmsGoogleIntegrator\Livewire;

use App\Classes\Integrators\IntegrationsManager;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\IntegrationConnection;
use App\Models\People\Person;
use App\Models\SubjectMatter\ClassSession;
use App\Models\SubjectMatter\SchoolClass;
use halestar\FabLmsGoogleIntegrator\Casts\ClassroomLink;
use halestar\FabLmsGoogleIntegrator\Connections\GoogleClassroomConnection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ClassPreferences extends Component
{
	public string $classes = '';
	public string $style = '';
	public Person $self;
	public SchoolClass $schoolClass;
	public ClassroomLink $classroomLink;
	public ?IntegrationConnection $connection;
	public array $gCourses;

	public function mount(SchoolClass $schoolClass)
	{
		$this->authorize('manage', $schoolClass);
		$this->self = auth()->user();
		$this->schoolClass = $schoolClass;
		$this->connection = null;
		foreach($schoolClass->sessions as $session)
			if($session->classManager instanceof GoogleClassroomConnection)
				$this->connection = $session->classManager;

		if(!$this->connection)
		{
			$this->redirect(route('learning.classes.settings', ));
			return;
		}
		$this->classroomLink = $this->connection->getClassLink($schoolClass)?? ClassroomLink::defaultInstance($schoolClass);
		foreach($this->connection->listCourses() as $course)
			$this->gCourses[$course->id] = $course->name;
	}

	public function updateGoogleClassesOptions()
	{
		foreach($this->connection->listCourses() as $course)
			$this->gCourses[$course->id] = $course->name;
	}

	public function savePreferences()
	{
		$this->connection->setClassLink($this->classroomLink);
		$this->connection->save();
	}

	public function createClassroom(ClassSession $classSession)
	{
		if($this->classroomLink->class_id == $classSession->class_id)
		{
			$classSession->classManager->createCourse($classSession);
			$this->classroomLink = $this->connection->getClassLink($this->schoolClass);
		}
	}

	public function syncCourseInformation()
	{
		$this->connection->syncCourseInformation($this->schoolClass);
	}

    public function render()
    {
        return view('google-integrator::livewire.class-preferences');
    }
}
