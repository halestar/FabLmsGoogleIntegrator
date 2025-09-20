<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Ai\ReadUrl;
use App\Classes\Integrators\SecureVault;
use App\Models\Ai\AiPrompt;
use App\Models\Integrations\Connections\AiConnection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Media\Document;

class GoogleAiConnection extends AiConnection
{
	public function getLlms(): array
	{
		return
		[
			'gemini-2.0-flash',
			'gemini-2.0-flash-lite'
		];
	}
	
	public function executePrompt(string $aiModel, AiPrompt $prompt): void
	{
		$vault = app()->make(SecureVault::class);
		//which key should we use?
		if($this->service->data->allow_user_system_ai)
			$key = $vault->retrieve('google', 'gemini_api');
		else
			$key = Crypt::decryptString($this->data->key);
		$file = $prompt->workFiles;
		$prompts = [];
		foreach($file as $f)
		{
			if($f->isImage())
				$prompt[] = Image::fromRawContent($f->lmsConnection->fileContents($f, $f->mime));
			else
				$prompt[] = Document::fromRawContent($f->lmsConnection->fileContents($f), $f->mime, $f->fileName());
		}
		
		$response = Prism::structured()
						->using(Provider::Gemini, $aiModel,
						[
							 'api_key' => $key,
							 'url' => 'https://generativelanguage.googleapis.com/v1beta/models',
						])
						->withSystemPrompt($prompt->systemPrompt->prompt)
						->withPrompt($prompt->prompt, $prompts)
						->usingTemperature($prompt->temperature)
						->withSchema($prompt->ai_promptable->getAiSchema())
						->asStructured();
		$prompt->last_results = $response->structured;
		$prompt->save();
	}
	
	public static function getInstanceDefault(): array
	{
		return ['key' => null];
	}
}