<?php

namespace halestar\FabLmsGoogleIntegrator\Traits;

use App\Classes\AI\AiSchema;
use App\Enums\AiSchemaType;
use App\Interfaces\AiPromptable;
use App\Interfaces\Fileable;
use App\Models\Ai\AiPrompt;
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
use Prism\Prism\ValueObjects\ProviderTool;
use Prism\Relay\Facades\Relay;

trait GoogleAi
{
    public function getLlms(): array
    {
        return
            [
                'gemini-2.0-flash',
                'gemini-2.0-flash-lite',
                'gemini-2.5-flash-lite',
                'gemini-2.5-flash',
            ];
    }

    private function logAiCall(string $aiModel, AiPrompt $prompt, $response)
    {
	    Log::channel('ai-calls')->info("************************************************** NEW AI CALL **************************************************");
        Log::channel('ai-calls')->info("Called Prompt: " . $prompt->className . " (id: " . $prompt->id . ") with property: " . $prompt->property . " and model {$aiModel}");
	    Log::channel('ai-calls')->info("Tools: " .
	                                   print_r(array_map(fn(Tool $tool) => ("Name: " . $tool->name() .
	                                                                        " Description: " . $tool->description()),
		                                   Relay::tools('local')), true));

	    // Access all tool calls across all steps
	    Log::channel('ai-calls')->info("Tool Results: " . count($response->toolCalls));
        foreach ($response->toolCalls as $toolCall)
        {
            Log::channel('ai-calls')->info("Called: {$toolCall->name}");
            Log::channel('ai-calls')->info("Arguments: " . json_encode($toolCall->arguments()));
        }
        // Access tool results
	    Log::channel('ai-calls')->info("Tool Results: " . count($response->toolResults));
        foreach ($response->toolResults as $result)
        {
            Log::channel('ai-calls')->info("Tool: " . $result->toolName);
            Log::channel('ai-calls')->info("Result: ". $result->result);
        }
        // Inspect individual steps
        foreach ($response->steps as $step)
        {
            Log::channel('ai-calls')->info("Step finish reason: {$step->finishReason->name}");
            if($step->toolCalls)
                Log::channel('ai-calls')->info("Tools called: " . count($step->toolCalls));

            if ($prompt->structured && $step->structured)
                Log::channel('ai-calls')->info("Contains structured data");
        }
		if($prompt->structured)
	        Log::channel('ai-calls')->info("Results: " . print_r($response->structured, true));
		else
	        Log::channel('ai-calls')->info("Results: " . $response->text);
	    Log::channel('ai-calls')->info("************************************************** END AI CALL **************************************************");
    }

	private function convertSchema(AiSchema $schema): mixed
	{
		if($schema->getType() == AiSchemaType::STRING)
			return new StringSchema($schema->getName(), $schema->getDescription(), $schema->isNullable());
		if($schema->getType() == AiSchemaType::BOOLEAN)
			return new BooleanSchema($schema->getName(), $schema->getDescription(), $schema->isNullable());
		if($schema->getType() == AiSchemaType::NUMBER)
			return new NumberSchema($schema->getName(), $schema->getDescription(), $schema->isNullable());
		if($schema->getType() == AiSchemaType::ARRAY)
			return new ArraySchema($schema->getName(), $schema->getDescription(), self::convertSchema($schema->getItems()), $schema->isNullable());
		if($schema->getType() == AiSchemaType::OBJECT)
		{
			$properties = [];
			foreach($schema->getProperties() as $key => $value)
				$properties[] = self::convertSchema($value);
			return new ObjectSchema($schema->getName(), $schema->getDescription(), $properties, $schema->getRequired(), false, $schema->isNullable());
		}
		//else, we assume it's null.
		return new StringSchema($schema->getName(), $schema->getDescription(), $schema->isNullable());
	}

    protected function executeGooglePrompt(string $aiModel, AiPrompt $prompt, AiPromptable $target, string $key)
    {
		$files = [];
		/*
		foreach($prompt->workFiles as $file)
		{
			if($file->isImage())
				$files[] = Image::fromUrl($file->url);
			elseif($file->isVideo())
				$files[] = Video::fromUrl($file->url);
			elseif($file->isAudio())
				$files[] = Audio::fromUrl($file->url);
			elseif($file->isDocument())
				$files[] = Document::fromUrl($file->url);

		}
	    /**********************
	     * TEMPORARY - until the MCP server can return files.
	     */
		if($target instanceof Fileable)
		{
			foreach($target->workFiles as $file)
			{
				if($file->isImage())
					$files[] = Image::fromRawContent($file->contents(), $file->mimeType);
				elseif($file->isVideo())
					$files[] = Video::fromRawContent($file->contents(), $file->mimeType);
				elseif($file->isAudio())
					$files[] = Audio::fromRawContent($file->contents(), $file->mimeType);
				elseif($file->isDocument())
					$files[] = Document::fromRawContent($file->contents(), $file->mimeType);
			}
		}
		if($prompt->structured)
		{
			$response = Prism::structured()
			                 ->using(
				                 Provider::Gemini, $aiModel,
				                 [
					                 'api_key' => $key,
					                 'url' => 'https://generativelanguage.googleapis.com/v1beta/models',
				                 ]
			                 )
			                 ->withMaxSteps(20)
			                 ->withSchema($this->convertSchema($target->getSchemaClass($prompt->property)))
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
}