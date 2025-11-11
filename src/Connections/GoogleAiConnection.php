<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiConnection;
use Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\ResponseMimeType;
use halestar\FabLmsGoogleIntegrator\Agents\LearningAgent;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use halestar\FabLmsGoogleIntegrator\Traits\GoogleAi;
use Illuminate\Support\Facades\Crypt;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Relay\Facades\Relay;

class GoogleAiConnection extends AiConnection
{
    use GoogleAi;
	public static function getInstanceDefault(): array
	{
		return ['key' => null];
	}
	
	public function executePrompt(string $aiModel, AiPrompt $prompt, AiPromptable $target): void
	{
		$vault = app()->make(SecureVault::class);
		//which key should we use?
		if($this->service->data->allow_user_system_ai)
			$key = $vault->retrieve('google', 'gemini_api');
		else
			$key = Crypt::decryptString($this->data->key);
        $this->executeGooglePrompt($aiModel, $prompt, $target, $key);
	}
}