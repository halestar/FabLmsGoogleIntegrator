<?php

namespace halestar\FabLmsGoogleIntegrator\Agents;

use App\Classes\Ai\ObjectSchemaDefinition;
use App\Interfaces\AiPromptable;
use App\Models\Ai\AiPrompt;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HttpClientOptions;

class LearningAgent extends Agent
{
	public function __construct(protected string $aiKey, protected string $aiModel, protected AiPrompt $prompt)	{}
	protected function provider(): AIProviderInterface
	{
		return new Gemini(key: $this->aiKey, model: $this->aiModel, httpOptions: new HttpClientOptions(timeout: 30));
	}
	
	public function instructions():string
	{
		return $this->prompt->system_prompt;
	}
	
	protected function getOutputClass(): string
	{
		return ($this->prompt->className)::getSchemaClass($this->prompt->property);
	}
	
	public static function askAi(string $key, string $model, AiPrompt $prompt, AiPromptable $target): ObjectSchemaDefinition
	{
		return LearningAgent::make($key, $model, $prompt)
			->structured(new UserMessage($prompt->renderPrompt($target)));
	}
	
}