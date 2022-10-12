<?php

namespace Visitares\Service\Media;

use Visitares\Entity\Media;

class VideoPreviewService{

	/** @var string */
	private $token = null;

	/** @var string */
	private $overrideDir = null;

	/**
	 * @param  Media $media
	 * @return void
	 */
	public function create(Media $media, $token = null){
		$this->token = $token;
		$this->createPreviewImage($media->getFilename(), 480);
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
	 * @param  string  $file
	 * @param  integer $height
	 * @return void
	 */
	protected function createPreviewImage($file, $height){
		$location = $this->getDir() . '/' . $file;
		$cmd = sprintf("\"%s\" -y -i \"%s\" -vf scale=-1:480 -vcodec mjpeg -vframes 1 -an -f rawvideo -ss `ffmpeg -i \"%s\" 2>&1 | grep Duration | awk '{print $2}' | tr -d , | awk -F ':' '{print $3/2}'` \"%s-preview.jpg\"", FFMPEG_BIN, $location, $location, $location);
		if(!FFMPEG_BIN){
			return null;
		}
		return @shell_exec($cmd);
	}

}