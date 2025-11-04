<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Classes\Integrators\SecureVault;
use App\Classes\Storage\DocumentFile;
use App\Classes\Storage\ExportFile;
use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\Connections\DocumentFilesConnection;
use App\Models\Integrations\IntegrationConnection;
use App\Models\Utilities\MimeType;
use Google\Client as GoogleClient;
use Google\Service\Drive\DriveFile;
use Google_Service_Drive;
use Illuminate\Http\UploadedFile;

class GoogleDocumentsConnection extends DocumentFilesConnection
{
	protected GoogleClient $client;
	protected ?Google_Service_Drive $drive = null;
	
	/**
	 * @inheritDoc
	 */
	public static function getSystemInstanceDefault(): array
	{
		return [];
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getInstanceDefault(): array
	{
		return [];
	}
	
	protected static function booted(): void
	{
		static::retrieved(function(IntegrationConnection $connection)
		{
			$vault = app()->make(SecureVault::class);
			$connection->client = new GoogleClient();
			$connection->client->setClientId($vault->retrieve('google', 'client_id'));
			$connection->client->setClientSecret($vault->retrieve('google', 'client_secret'));;
			//find the google auth service and connect to the user.
			/** @var GoogleAuthConnection $authConnection */
			$authConnection = $connection->integrator
				->services()
				->ofType(IntegratorServiceTypes::AUTHENTICATION)
				->first()
				?->connect($connection->person);
			if($authConnection && $authConnection->hasActiveToken())
			{
				$connection->client->setAccessToken($authConnection->data->oauth_token);
				$connection->drive = new Google_Service_Drive($connection->client);
			}
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function rootFiles(array $mimeTypes = []): array
	{
		if(!$this->drive) return [];
		$optParams =
			[
				'q' => "'root' in parents and trashed=false",
				'fields' => 'nextPageToken, files(id, name, mimeType, thumbnailLink, fullFileExtension)',
				'pageSize' => 100,
			];
		
		$results = $this->drive->files->listFiles($optParams);
		$documentFiles = [];
		foreach($results->getFiles() as $gFile)
			$documentFiles[] = $this->createDocumentFile($gFile);
		return $documentFiles;
	}
	
	private function createDocumentFile(DriveFile $file): ?DocumentFile
	{
		if($file->mimeType == 'application/vnd.google-apps.folder')
		{
			//make a folder
			return new DocumentFile
			(
				$this->person->school_id,
				true,
				$file->name,
				$this->id,
				$file->id,
				MimeType::FOLDER_HTML,
				'',
				0,
				false,
				false,
				false,
				false
			);
		}
		return new DocumentFile
		(
			$this->person->school_id,
			false,
			$file->name,
			$this->id,
			$file->id,
			$file->thumbnailLink ? '<img src="' . $file->thumbnailLink . '" alt="' . $file->name . '" />' :
				MimeType::find($file->mimeType)?->icon,
			$file->mimeType,
			$file->size ?? 0,
			false,
			false,
			false,
			false
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function files(DocumentFile $directory, array $mimeTypes = []): array
	{
		$optParams =
			[
				'q' => "'" . $directory->path . "' in parents and trashed=false",
				'fields' => 'nextPageToken, files(id, name, mimeType, thumbnailLink, fullFileExtension, parents)',
				'pageSize' => 100,
			];
		
		$results = $this->drive->files->listFiles($optParams);
		$documentFiles = [];
		foreach($results->getFiles() as $gFile)
			$documentFiles[] = $this->createDocumentFile($gFile);
		return $documentFiles;
	}
	
	/**
	 * @inheritDoc
	 */
	public function file(string $path): ?DocumentFile
	{
		$file = $this->drive->files->get($path, ['fields' => 'id, name, mimeType, thumbnailLink, fullFileExtension']);
		return $this->createDocumentFile($file);
	}
	
	/**
	 * @inheritDoc
	 */
	public function parentDirectory(DocumentFile $file): ?DocumentFile
	{
		$file = $this->drive->files->get($file->path, ['fields' => 'parents']);
		if(!$file->parent | !is_array($file->parents) || count($file->parents) == 0)
			return null;
		$parent_id = $file->parents[0];
		$file = $this->drive->files->get($parent_id,
			['fields' => 'id, name, mimeType, thumbnailLink, fullFileExtension']);
		return $this->createDocumentFile($file);
	}
	
	/**
	 * @inheritDoc
	 */
	public function previewFile(DocumentFile $file): string
	{
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function deleteFile(DocumentFile $file): void {}
	
	/**
	 * @inheritDoc
	 */
	public function changeName(DocumentFile $file, string $name): void {}
	
	/**
	 * @inheritDoc
	 */
	public function changeParent(DocumentFile $file, DocumentFile $newParent = null): void {}
	
	/**
	 * @inheritDoc
	 */
	public function canPersistFiles(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function persistFolder(string $name, DocumentFile $parent = null): ?DocumentFile
	{
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function persistFile(UploadedFile $file, DocumentFile $parent = null): ?DocumentFile
	{
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function exportFile(DocumentFile $file, array $preferMime = []): ?ExportFile
	{
		//we can do a straight download if the file is an image file or a pdf
		if(str_starts_with($file->mimeType, 'image/') || $file->mimeType == 'application/pdf')
		{
			$response = $this->drive->files->get($file->path, ['alt' => 'media']);
			$fileContents = $response->getBody()
			                         ->getContents();
			switch($file->mimeType)
			{
				case 'image/jpeg':
					$ext = "jpg";
					break;
				case 'image/png':
					$ext = "png";
					break;
				case 'image/gif':
					$ext = "gif";
					break;
				case 'application/pdf':
					$ext = "pdf";
					break;
				default:
					$ext = "pdf";
			}
			return new ExportFile($file->name, $fileContents, $file->mimeType, $ext, $file->size);
		}
		//any other kind of file, we download as a PDF
		$response = $this->drive->files->export($file->path, 'application/pdf', ['alt' => 'media']);
		$fileContents = $response->getBody()
		                         ->getContents();
		return new ExportFile($file->name, $fileContents, 'application/pdf', 'pdf', $file->size);
	}
}