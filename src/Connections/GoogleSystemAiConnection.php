<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Ai\ReadUrl;
use App\Classes\Integrators\SecureVault;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiSystemConnection;
use App\Models\Integrations\IntegrationConnection;
use Google\Client as GoogleClient;
use Google_Service_Aiplatform;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\ValueObjects\ProviderTool;

class GoogleSystemAiConnection extends AiSystemConnection
{
	public function getLlms(): array
	{
		return
		[
			'gemini-2.0-flash',
			'gemini-2.0-flash-lite'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return [];
	}
	
	public function executePrompt(string $aiModel, AiPrompt $prompt): void
	{
		$vault = app()->make(SecureVault::class);
		$response = Prism::structured()
						->using(Provider::Gemini, $aiModel,
						[
							 'api_key' => $vault->retrieve('google', 'gemini_api'),
							 'url' => 'https://generativelanguage.googleapis.com/v1beta/models',
						])
						->withSystemPrompt($prompt->systemPrompt->prompt)
						->withPrompt($prompt->prompt)
						->usingTemperature($prompt->temperature)
						->withSchema($prompt->ai_promptable->getAiSchema())
						->asStructured();
		$prompt->last_results = $response->structured;
		$prompt->save();
	}
}