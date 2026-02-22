<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Casts\Ai\ProviderOptions;
use App\Classes\AI\AiSchema;
use App\Classes\AI\ProviderOption;
use App\Classes\Integrators\SecureVault;
use App\Enums\AiSchemaType;
use App\Interfaces\AiPromptable;
use App\Interfaces\Fileable;
use App\Models\Ai\AiPrompt;
use App\Models\Ai\Llm;
use App\Models\Integrations\Connections\AiConnection;
use halestar\FabLmsGoogleIntegrator\Services\GoogleAiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Media\Audio;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Media\Video;
use Prism\Relay\Facades\Relay;

class GoogleAiConnection extends AiConnection
{
	public static function getInstanceDefault(): array
	{
		return ['api_key' => null];
	}

	public static function getSystemInstanceDefault(): array
	{
		return ['api_key' => null];
	}
	
	public function executePrompt(Llm $aiModel, AiPrompt $prompt, AiPromptable $target): void
	{
		$key = Crypt::decryptString($this->data->api_key);
        $this->executeGooglePrompt($aiModel->model_id, $prompt, $target, $key);
	}

	public function getLlms(): array
	{
		$key = Crypt::decryptString($this->data->api_key);
		$llms = [];
		try
		{
			$payload = ['key' => $key];
			do
			{
				$response = Http::get(GoogleAiService::GEMINI_BASE_URL . "/models", $payload);
				if($response->ok())
				{
					$llms += $response->json('models', []);
					if($response->json('nextPageToken', null))
						$payload['pageToken'] = $response->json('nextPageToken');
					else
						$payload['pageToken'] = null;
				}
			} while($response->ok() && $payload['pageToken'] !== null);
		}
		catch (\Exception $e)
		{
			Log::error("Failed to fetch Google LLMs: {$e->getMessage()}");
		}
		return $llms;
	}

	public function defaultProviderOptions(): ProviderOptions
	{
		return new ProviderOptions;
	}

	public function refreshLlms(bool $reset = false): void
	{
		$models = $this->getLlms();
		$idx = 0;
		$providerOptions = $this->defaultProviderOptions();
		$ids = [];
		foreach($models as $model)
		{
			$llm = Llm::where('connection_id', $this->id)->where('model_id', $model['name'])->first();
			if(!$llm)
			{
				$llm = new Llm();
				$llm->model_id = $model['name'];
				$llm->connection_id = $this->id;
				$llm->name = $model['displayName'];
				$llm->description = $model['description'] ?? null;
				$llm->hide = false;
				$llm->order = $idx;
				$llm->provider_options = $providerOptions;
				$llm->save();
			}
			elseif($reset)
			{
				$llm->name = $model['displayName'];
				$llm->description = $model['description'] ?? null;
				$llm->hide = false;
				$llm->order = $idx;
				$llm->provider_options = $providerOptions;
				$llm->save();
			}
			$ids[] = $llm->id;
			$idx++;
		}
		Llm::where('connection_id', $this->id)->whereNotIn('id', $ids)->delete();
	}



	protected function executeGooglePrompt(string $aiModel, AiPrompt $prompt, AiPromptable $target, string $key)
	{
		$files = $this->extractFiles($prompt);
		if($target instanceof Fileable)
			$files += $this->extractFiles($target);
		if($prompt->structured)
		{
			$response = Prism::structured()
				->using(
					Provider::Gemini, $aiModel,
					[
						'api_key' => $key,
						'url' => 'https://generativelanguage.googleapis.com/v1beta',
					]
				)
				->withMaxSteps(20)
				->withSchema($target->getSchema($prompt->property))
				->withSystemPrompt($prompt->system_prompt)
				->withPrompt($prompt->renderPrompt($target), $files)
				->usingTemperature($prompt->temperature)
				->asStructured();
			$prompt->last_results = $response->structured;
		}
		else
		{
			$response = Prism::text()
				->using(
					Provider::Gemini, $aiModel,
					[
						'api_key' => $key,
						'url' => 'https://generativelanguage.googleapis.com/v1beta/models',
					]
				)
				->withTools(Relay::tools('local'))
				->withSystemPrompt($prompt->system_prompt)
				->withPrompt($prompt->renderPrompt($target), $files)
				->usingTemperature($prompt->temperature)
				->withMaxSteps(20)
				->asText();
			$prompt->last_results = $response->text;
		}
		$prompt->save();
		$this->logAiCall($aiModel, $prompt, $response);
	}

	public function validProviderOption(Llm $llm, ProviderOption $option): bool
	{
		return true;
	}
}