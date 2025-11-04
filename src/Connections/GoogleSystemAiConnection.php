<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiSystemConnection;
use Google_Service_Aiplatform;
use halestar\FabLmsGoogleIntegrator\Agents\LearningAgent;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class GoogleSystemAiConnection extends AiSystemConnection
{
	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return [];
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
		$prompt->last_results = LearningAgent::askAi($vault->retrieve('google', 'gemini_api'), $aiModel, $prompt, $target);
		$prompt->save();
	}
}