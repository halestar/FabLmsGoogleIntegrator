<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Classes\Storage\DocumentFile;
use App\Enums\IntegratorServiceTypes;
use App\Interfaces\Fileable;
use App\Models\Integrations\Connections\WorkFilesConnection;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Utilities\WorkFile;
use Google\Client as GoogleClient;
use Google\Service\Drive\DriveFile;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleWorkConnection extends WorkFilesConnection
{
	protected GoogleClient $client;
	protected ?Google_Service_Drive $drive = null;
	
	protected static function booted(): void
	{
		static::retrieved(function(IntegrationConnection $connection)
		{
			$vault = app()->make(SecureVault::class);
			$connection->client = new GoogleClient();
			$settings = json_decode($vault->retrieveFile('google', 'service_account'), true);
			$settings['type'] = 'service_account';
			$connection->client->setAuthConfig($settings);
			$connection->client->addScope(GoogleIntegrationServices::WORK->scopes());
			$connection->client->setSubject($connection->service->data->service_account);
			$connection->drive = new Google_Service_Drive($connection->client);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return
			[
				'instances' => [],
			];
	}
	
	private function getInstance(string $instance)
	{
		$instances = $this->data->instances;
		if($instances instanceof \stdClass)
			$instances = json_decode(json_encode($instances), true);
		if(!isset($instances[$instance]))
		{
			//in this case we will need to create the instance, which means creating the folder
			//in the drive account.
			$metadata = new Google_Service_Drive_DriveFile(
				[
					'name' => $instance,
					'mimeType' => 'application/vnd.google-apps.folder',
				]);
			$folder = $this->drive->files->create($metadata, ['fields' => 'id, name']);
			$instances[$instance] = $folder->getId();
			$this->data->instances = $instances;
			$this->save();
		}
		return $instances[$instance];
	}
	
	/**
	 * @inheritDoc
	 */
	public function persistFile(Fileable $fileable, DocumentFile $file, bool $hidden = false): ?WorkFile
	{
		$exportFile = $file->getExportFile();
		$gFile = new DriveFile();
		$parentFolder = $this->getInstance($fileable->getWorkStorageKey()->value);
		$gFile->setName($exportFile->name);
		$gFile->setParents([$parentFolder]);
		
		$gFile = $this->drive->files->create(
			$gFile,
			[
				'data' => $exportFile->contents,
				'mimeType' => 'application/octet-stream',
				'uploadType' => 'multipart',
			]);
		$workFile = new WorkFile();
		$workFile->name = $gFile->getName();
		$workFile->connection_id = $this->id;
		$workFile->path = $gFile->getId();
		$workFile->mime = $gFile->getMimeType();
		$workFile->size = $file->size;
		$workFile->extension = $exportFile->extension;
		$workFile->hidden = $hidden;
		$workFile->public = $fileable->shouldBePublic();
		$workFile->save();
		//finally, we link the file
		$fileable->workFiles()
		         ->attach($workFile);
		return $workFile;
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function deleteFile(WorkFile $file): void
	{
		$this->drive->files->delete($file->path);
	}
	
	/**
	 * @inheritDoc
	 */
	public function download(WorkFile $file): StreamedResponse
	{
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $file->mime);
		header('Content-Disposition: ' . ($file->shouldAttach() ? 'attachment; filename="' . $file->fileName() : 'inline'));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $file->size);
		$response = $this->drive->files->get($file->path, ['alt' => 'media']);
		$stream = $response->getBody()->getContents();
		return new StreamedResponse(function () use ($stream) {
			echo $stream;
		});
		
	}
	
	/**
	 * @inheritDoc
	 */
	public function fileContents(WorkFile $file): ?string
	{
		return $this->drive->files->get($file->path, ['alt' => 'media'])->getBody()->getContents();
	}
}