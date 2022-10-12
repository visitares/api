<?php

namespace Visitares\API;

use DateTime;
use Visitares\Storage\Facade\InstanceStorageFacade;
use Visitares\Entity\{ Catalog, CatalogAttribute, CatalogEntry, CatalogEntryAttribute };

class CatalogsController{

	private $storage;
	private $em;
	private $pdo;
	private $catalogs;
	private $entries;

	/**
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 */
	public function __construct(
		InstanceStorageFacade $storage,
		$token
	){
		$this->storage = $storage;
		$this->storage->setToken($token);
		$this->em = $this->storage->getEntityManager();
		$this->pdo = $this->em->getConnection()->getWrappedConnection();
		$this->catalogs = $this->em->getRepository(Catalog::class);
		$this->entries = $this->em->getRepository(CatalogEntry::class);
	}


	/**
	 * CATALOGS API
	 */

	/**
	 * @param integer $id
	 * @return array|null
	 */
	public function getCatalogById(int $id){
		if(!($catalog = $this->catalogs->findOneById($id))){
			return null;
		}
		return[
			'id' => $catalog->getId(),
			'entries' => $catalog->getEntries()->count(),
			'allowInstructions' => $catalog->getAllowInstructions(),
			'name' => $catalog->getNameTranslation(),
			'description' => $catalog->getDescriptionTranslation(),
			'attributes' => $catalog->getAttributes()->toArray(),
		];
	}

	/**
	 * @param array $data
	 * @return array|bool
	 */
	public function saveCatalog(array $data){
		$id = $data['id'] ?? null;
		$catalog = new Catalog();

		if($id && !($catalog = $this->catalogs->findOneById($id))){
			return false;
		}

		if($id){
			$catalog->setModificationDate(new DateTime());
		}
		foreach($data['name'] as $lang => $content){
			$catalog->setName($lang, $content ?? '');
		}
		foreach($data['description'] as $lang => $content){
			$catalog->setDescription($lang, $content ?? '');
		}

		$catalog->setAllowInstructions($data['allowInstructions'] ?? false);

		$this->em->persist($catalog);
		$this->em->flush();

		if(!$catalog->getId()){
			return[ 'error' => true ];
		}

		$this->saveCatalogAttributes($catalog, $data['attributes'] ?? []);
		$this->em->clear();

		return $this->getCatalogById($catalog->getId());
	}

	/**
	 * @param Catalog $catalog
	 * @param array $attributes
	 * @return void
	 */
	public function saveCatalogAttributes(Catalog $catalog, array $attributes){
		$position = 0;
		foreach($attributes as $attribute){
			$id = $attribute['id'] ?? null;
			$catalogAttribute = new CatalogAttribute();

			if($id && !($catalogAttribute = $this->em->getRepository(CatalogAttribute::class)->findOneById($attribute['id']))){
				continue;
			}

			if($attribute['delete'] ?? false){
				$this->em->remove($catalogAttribute);
				continue;
			}

			$catalogAttribute->setCatalog($catalog);
			$catalogAttribute->setPosition($position++);
			$catalogAttribute->setType($attribute['type']);
			foreach($attribute['name'] as $lang => $content){
				$catalogAttribute->setName($lang, $content ?? '');
			}
			$this->em->persist($catalogAttribute);
		}

		$this->em->flush();
	}

	/**
	 * @param integer $id
	 * @return bool
	 */
	public function removeCatalog(int $id){
		if(!($catalog = $this->catalogs->findOneById($id))){
			return false;
		}

		$pdo = $this->em->getConnection()->getWrappedConnection();
		$statement = $pdo->prepare('DELETE FROM `catalog` WHERE id = :id');
		$statement->execute([
			'id' => $id,
		]);

		return true;
	}

	/**
	 * @param array $ids
	 * @return bool
	 */
	public function removeManyCatalogs(array $ids){
		foreach($ids as $id){
			if(!($catalog = $this->catalogs->findOneById($id))){
				continue;
			}
			$this->em->remove($catalog);
		}
		$this->em->flush();
		return true;
	}

	
	/**
	 * ENTRIES API
	 */

	/**
	 * @param integer $id
	 * @return array|null
	 */
	public function getCatalogEntryById(int $id){
		if(!($entry = $this->entries->findOneById($id))){
			return null;
		}
		$array = $entry->toArray();
		$array['catalog'] = $entry->getCatalog();
		$array['attributes'] = $entry->getAttributes()->toArray();
		return $array;
	}

	/**
	 * @param array $data
	 * @return array|null
	 */
	public function saveCatalogEntry(array $data = []){
		$id = $data['id'] ?? null;
		$entry = new CatalogEntry();

		if($id && !($entry = $this->entries->findOneById($id))){
			return false;
		}

		if($id){
			$entry->setModificationDate(new DateTime());
		}

		$entry->setCatalog($this->em->getReference(Catalog::class, $data['catalog']['id']));
		foreach($data['name'] as $lang => $content){
			$entry->setName($lang, $content ?? '');
		}
		foreach($data['description'] as $lang => $content){
			$entry->setDescription($lang, $content ?? '');
		}

		$this->em->persist($entry);
		$this->em->flush();
		
		if(!$entry->getId()){
			return[ 'error' => true ];
		}

		$this->saveCatalogEntryAttributes($entry, $data['attributes'] ?? []);
		$this->em->clear();

		return $this->getCatalogEntryById($entry->getId());
	}

	/**
	 * @param Catalog $catalog
	 * @param array $attributes
	 * @return void
	 */
	public function saveCatalogEntryAttributes(CatalogEntry $entry, array $attributes){
		$position = 0;
		foreach($attributes as $attribute){
			$id = $attribute['id'] ?? null;
			$entryAttribute = new CatalogEntryAttribute();

			if($id){
				$entryAttribute = $this->em->getRepository(CatalogEntryAttribute::class)->findOneById($id);
				$entryAttribute->setModificationDate(new DateTime());
			}

			$entryAttribute->setEntry($entry);
			$entryAttribute->setAttribute($this->em->getReference(CatalogAttribute::class, $attribute['attribute']));
			$entryAttribute->setIsActive($attribute['isActive'] ?? false);
			foreach($attribute['value'] as $lang => $content){
				$entryAttribute->setValue($lang, $content ?? '');
			}

			$this->em->persist($entryAttribute);
		}

		$this->em->flush();
	}

	/**
	 * @param integer $id
	 * @return bool
	 */
	public function removeCatalogEntry(int $id){
		if(!($catalogEntry = $this->entries->findOneById($id))){
			return false;
		}
		$this->em->remove($catalogEntry);
		$this->em->flush();
		return true;
	}

	/**
	 * @param integer $id
	 * @return bool
	 */
	public function removeManyCatalogEntries(int $id){
		foreach($ids as $id){
			if(!($catalogEntry = $this->entries->findOneById($id))){
				continue;
			}
			$this->em->remove($catalogEntry);
		}
		$this->em->flush();
		return true;
	}

}