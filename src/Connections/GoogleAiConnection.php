<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiConnection;
use halestar\FabLmsGoogleIntegrator\Agents\LearningAgent;
use Illuminate\Support\Facades\Crypt;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\Media\Image;

class GoogleAiConnection extends AiConnection
{
	public static function getInstanceDefault(): array
	{
		return ['key' => null];
	}
	
	public function getLlms(): array
	{
		return
			[
				'gemini-2.0-flash',
				'gemini-2.0-flash-lite'
			];
	}
	
	public function executePrompt(string $aiModel, AiPrompt $prompt, AiPromptable $target): void
	{
		$vault = app()->make(SecureVault::class);
		//which key should we use?
		if($this->service->data->allow_user_system_ai)
			$key = $vault->retrieve('google', 'gemini_api');
		else
			$key = Crypt::decryptString($this->data->key);
		
		$prompt->last_results = LearningAgent::askAi($key, $aiModel, $prompt, $target);
		$prompt->save();
	}
}