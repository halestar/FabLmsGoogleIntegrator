<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Mail\SchoolMail;
use App\Models\Integrations\Connections\EmailConnection;
use App\Models\Integrations\IntegrationConnection;
use App\Models\People\Person;
use Google\Client as GoogleClient;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Support\Facades\Log;

class GoogleEmailConnection extends EmailConnection
{

	protected GoogleClient $client;
	protected ?Google_Service_Gmail $service;

	protected static function booted(): void
	{
		static::retrieved(function(IntegrationConnection $connection)
		{
			$vault = app()->make(SecureVault::class);
			$connection->client = new GoogleClient();
			$settings = json_decode($vault->retrieveFile('google', 'service_account'), true);
			$settings['type'] = 'service_account';
			$connection->client->addScope(GoogleIntegrationServices::EMAIL->scopes());
			$connection->client->setAuthConfig($settings);
			$connection->client->setApplicationName(config('app.name'));
			$connection->client->setSubject($connection->data->account);
			$connection->service = new Google_Service_Gmail($connection->client);
		});
	}
	/**
	 * @inheritDoc
	 */
	public function sendToPerson(Person|string $recipient, SchoolMail $mail): void
	{
		try
		{
			$message = base64_encode($mail->mailableToRfc2822($recipient));
			$gmailMessage = new Google_Service_Gmail_Message();
			$gmailMessage->setRaw($message);
			$sentMessage = $this->service->users_messages->send(
				$recipient instanceof Person ? $recipient->email : $recipient, $gmailMessage
			);
			Log::info('Sent message: ' . $sentMessage->getId());
		}
		catch(\Exception $e)
		{
			Log::error('Failed to send email: ' . $e->getMessage());
		}
	}

	public function sendToPersonSimple(Person|string $recipient, string $subject, string $body): void
	{
		try
		{
			$message = rtrim(strtr(base64_encode
			(
				"To: " . $recipient instanceof Person ? $recipient->email : $recipient . "\r\n" .
				                                                            "Subject: =?utf-8?B?" . $subject . "?=\r\n" .
				                                                            "\r\n" . $body
			), '+/', '-_'), '=');
			$gmailMessage = new Google_Service_Gmail_Message();
			$gmailMessage->setRaw($message);
			$sentMessage = $this->service->users_messages->send(
				$recipient instanceof Person ? $recipient->email : $recipient, $gmailMessage
			);
			Log::info('Sent message: ' . $sentMessage->getId());
		}
		catch(\Exception $e)
		{
			Log::error('Failed to send email: ' . $e->getMessage());
		}
	}

	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return
			[
				'account' => '',
			];
	}
}