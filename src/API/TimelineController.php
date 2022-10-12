<?php

namespace Visitares\API;

use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\Timeline;
use Visitares\Entity\Instance;

class TimelineController{

	private $storage = null;
	private $em = null;
	private $timelines = null;
	private $instances = null;

	public function __construct(
		SystemStorageFacade $storage
	){
		$this->storage = $storage;
		$this->em = $this->storage->getEntityManager();
		$this->timelines = $this->em->getRepository(Timeline::class);
		$this->instances = $this->em->getRepository(Instance::class);
	}

	/**
	 * @param  array $data
	 * @return Timeline
	 */
	public function getById($id){
		if($timeline = $this->timelines->findOneById($id)){

			$instances = $this->instances->findBy([
				'timeline' => $timeline
			]);

			$timeline->instances = array_map(function($instance){
				return $instance->getId();
			}, $instances);

			return $timeline;
		}
		return null;
	}

	/**
	 * @param  array $data
	 * @return Timeline
	 */
	public function store(array $data){
		$timeline = new Timeline;
		$timeline->setIsActive($data['isActive']);
		$timeline->setName($data['name']);
		$timeline->setShortDescription(isset($data['shortDescription']) ? $data['shortDescription'] : null);
		$this->em->persist($timeline);
		$this->em->flush();

		$this->updateInstanceJoins($timeline, $data['instances']);

		return $this->getById($timeline->getId());
	}

	/**
	 * @param  string $id
	 * @param  array  $data
	 * @return Timeline
	 */
	public function update($id, array $data){
		if($timeline = $this->timelines->findOneById($id)){
			$timeline->setIsActive($data['isActive']);
			$timeline->setName($data['name']);
			$timeline->setShortDescription(isset($data['shortDescription']) ? $data['shortDescription'] : null);
			$this->em->flush();

			$this->updateInstanceJoins($timeline, $data['instances']);

			return $this->getById($timeline->getId());
		}
		return null;
	}

	/**
	 * @param  array $ids
	 * @return void
	 */
	protected function updateInstanceJoins(Timeline $timeline, array $ids){
		$instances = $this->storage->instance->findBy([
			'timeline' => $timeline
		]);

		foreach($instances as $instance){
			if(!in_array($instance->getId(), $ids)){
				$instance->setTimeline(null);
			}
		}

		$instances = $this->storage->instance->findBy([
			'id' => $ids
		]);

		foreach($instances as $instance){
			$instance->setTimeline($timeline);
		}

		$this->em->flush();
	}

	/**
	 * @param  integer $id
	 * @return boolean
	 */
	public function remove($id){
		if($timeline = $this->timelines->findOneById($id)){
			$this->em->remove($timeline);
			$this->em->flush();
			return true;
		}
		return false;
	}

	/**
	 * @param  array $ids
	 * @return boolean
	 */
	public function removeMany(array $ids){
		if($timelines = $this->timelines->findBy(['id' => $ids])){
			foreach($timelines as $timeline){
				$this->em->remove($timeline);
			}
			$this->em->flush();
			return true;
		}
		return false;
	}

}