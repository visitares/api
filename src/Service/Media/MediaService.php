<?php

namespace Visitares\Service\Media;

use DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Visitares\Entity\Media;

class MediaService{

	/** @var string */
	private $token = null;

	/** @var string */
	private $overrideDir = null;

	/**
	 * @param string $token
	 */
	public function __construct(
		$token = null
	){
		$this->token = $token;
	}

	/**
	 * @param string $dir
	 */
	public function setOverrideDir($dir){
		$this->overrideDir = $dir;
	}

	/**
	 * @return string
	 */
	protected function getDir(){
		return !$this->overrideDir ? sprintf(APP_DIR_ROOT . '/web/shared/user/%s/media', $this->token) : $this->overrideDir;
	}

	/**
	 * @param  UploadedFile $file
	 * @return array
	 */
	public function store(UploadedFile $file){
		$hash = hash('sha256', $file->getClientOriginalName() . (new DateTime)->format('Y-m-d H:i:s') . $file->getSize());

		$dir = $this->getDir();
		$filename = sprintf('%s.%s', $hash, $file->getClientOriginalExtension());
		$location = sprintf('%s/%s', $dir, $filename);
		if(!file_exists($dir)){
			@mkdir($dir, 0777, true);
		}

		$file->move($dir, $filename);

		return (object)[
			'location' => $location,
			'filename' => $filename
		];
	}

	/**
	 * @param  UploadedFile $file
	 * @return array
	 */
	public function removeFile(Media $media){
		if(!$media->getFilename()){
			return false;
		}

		$location = sprintf('%s/%s', $this->getDir(), $media->getFilename());
		if(file_exists($location)){
			@unlink($location);

			if(strpos($media->getMime(), 'video/') !== false){
				$previewImage = sprintf('%s/%s-preview.jpg', $this->getDir(), $media->getFilename());
				if(file_exists($previewImage)){
					@unlink($previewImage);
				}
			}

			$media->setFilename(null);
			$media->setOriginalFilename(null);
			$media->setFilesize(null);
			$media->setMime(null);
			$media->setExt(null);
			return true;
		}

		return false;
	}

}