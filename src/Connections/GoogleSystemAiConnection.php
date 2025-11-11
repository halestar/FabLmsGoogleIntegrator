<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiSystemConnection;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use halestar\FabLmsGoogleIntegrator\Traits\GoogleAi;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Relay\Facades\Relay;

class GoogleSystemAiConnection extends AiSystemConnection
{
    use GoogleAi;
	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return [];
	}

    public function executePrompt(string $aiModel, AiPrompt $prompt, AiPromptable $target): void
    {
        $vault = app()->make(SecureVault::class);
        $this->executeGooglePrompt($aiModel, $prompt, $target, $vault->retrieve('google', 'gemini_api'));
	}
}