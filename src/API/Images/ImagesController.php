<?php

namespace Visitares\API\Images;

use DateTime;
use Visitares\Entity\Image;
use Visitares\Entity\ImageGroup;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

class ImagesController{

	private $storage = null;
	private $instanceStorage = null;

	public function __construct(
		SystemStorageFacade $storage,
		InstanceStorageFacade $instanceStorage
	){
		$this->storage = $storage;
		$this->instanceStorage = $instanceStorage;
	}

	public function getAll(){
		return $this->storage->image->find();
	}

	public function getByGroup($id){
		return $this->storage->image->find([
			'groupId' => $id
		]);
	}

	public function get($id){
		return $this->storage->image->findById($id);
	}

	public function store(array $data){
		$image = new Image;
		$image->setCreationDate(new DateTime);
		$image->setGroupId($data['group']);

		// save image to disk ..
		$checksum = hash('sha256', $data['uri'] . $image->getCreationDate()->format('Y-m-d H:i:s'));
		$image->setFilename($checksum);
		$this->saveImageToDisk($data['uri'], APP_DIR_ROOT . '/web/shared/images/' . $checksum . '.png');

		// if image is saved successful, persist row to db
		$this->storage->store($image);
		$this->storage->apply();

		return $this->get($image->getId());
	}

	public function update($id, array $data){
		if(!$image = $this->storage->image->findById($id)){
			return false;
		}

		$image->setModificationDate(new DateTime);
		$image->setGroupId($data['group']);

		// replace image ..
		if(file_exists(APP_DIR_ROOT . '/web/shared/images/' . $image->getFilename() . '.png')){
			unlink(APP_DIR_ROOT . '/web/shared/images/' . $image->getFilename() . '.png');
		}
		$checksum = hash('sha256', $data['uri'] . $image->getModificationDate()->format('Y-m-d H:i:s'));
		$image->setFilename($checksum);
		$this->saveImageToDisk($data['uri'], APP_DIR_ROOT . '/web/shared/images/' . $checksum . '.png');

		// if image is saved successful, persist row to db
		$this->storage->store($image);
		$this->storage->apply();

		return $this->get($image->getId());
	}

	public function remove($id){
		if(!$image = $this->storage->image->findById($id)){
			return false;
		}

		$instances = $this->storage->instance->findAll();
		foreach($instances as $instance){
			$this->instanceStorage->setToken($instance->getToken());
			$categories = $this->instanceStorage->category->find([
				'iconId' => $id
			]);
			if(count($categories)){
				return false;
			}
		}
		
		if(file_exists(APP_DIR_ROOT . '/web/shared/images/' . $image->getFilename() . '.png')){
			unlink(APP_DIR_ROOT . '/web/shared/images/' . $image->getFilename() . '.png');
		}

		$this->storage->remove($image);
		$this->storage->apply();

		return true;
	}

	protected function saveImageToDisk($uri, $dest){
		list(, $base64) = explode(',', $uri);
		$image = imagecreatefromstring( base64_decode($base64) );
		imagealphablending($image, false);
		imagesavealpha($image, true);
		imagepng($image, $dest);
		return $dest; 
	}

}