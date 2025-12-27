<?php
namespace halestar\FabLmsGoogleIntegrator\Casts;

use App\Interfaces\Synthesizable;
use App\Models\SubjectMatter\SchoolClass;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class ClassroomLink implements CastsAttributes, Synthesizable
{
	//The is of the SchoolClass model this link is for
	public string $class_id;
	//This is the link to Google, it is a key-value pair of session_id => classroom id
	public array $sessions;
	//whether it should sync basic classroom data
	public bool $sync_basic = true;
	//Whether it should sync students
	public bool $sync_students = true;
	//whether it should sync assignments
	public bool $sync_assignments = true;

	public static function defaultInstance(SchoolClass $schoolClass): static
	{
		$link = new static();
		$link->class_id = $schoolClass->id;
		$link->sessions = $schoolClass->sessions->pluck('id')->mapWithKeys(fn($id) => [$id => null])->toArray();
		return $link;
	}

	/**
	 * @inheritDoc
	 */
	public function get(\Illuminate\Database\Eloquent\Model $model, string $key, mixed $value, array $attributes)
	{
		if(!$value)
			return [];
		$json = json_decode($value, true);
		if(!is_array($json))
			return [];
		$links = [];
		foreach($json as $schoolId => $link)
			$links[$schoolId] = ClassroomLink::hydrate($link);
		return $links;
	}

	/**
	 * @inheritDoc
	 */
	public function set(\Illuminate\Database\Eloquent\Model $model, string $key, mixed $value, array $attributes)
	{
		$data = [];
		foreach($value as $schoolId => $link)
			$data[$schoolId] = $link->toArray();
		return json_encode($data);
	}

	public function toArray(): array
	{
		return get_object_vars($this);
	}

	public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}

	public static function hydrate(array $data): static
	{
		$link = new static();
		foreach($data as $key => $value)
			$link->$key = $value;
		return $link;
	}
}