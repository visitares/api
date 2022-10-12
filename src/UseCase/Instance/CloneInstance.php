<?php

namespace Visitares\UseCase\Instance;

use stdClass;
use RuntimeException;
use Doctrine\ORM\Proxy\ProxyFactory;
use ErrorException;
use Visitares\Entity\AbstractEntity;
use Visitares\Entity\Category;
use Visitares\Entity\Client;
use Visitares\Entity\Form;
use Visitares\Entity\Media;
use Visitares\Entity\MediaGroup;
use Visitares\Entity\Group;
use Visitares\Entity\Instance;
use Visitares\Entity\Language;
use Visitares\Entity\Translation;
use Visitares\Entity\Translated;
use Visitares\Storage\Facade\InstanceStorageFacade;

/**
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class CloneInstance extends CreateInstance{

	/**
	 * @var EntityManager
	 */
	protected $source = null;

	/**
	 * @var EntityManager
	 */
	protected $dest = null;

	/**
	 * @param  Instance $this->source
	 * @param  Instance $this->target
	 * @return boolean
	 */
	public function cloneFrom(Instance $source, Instance $target){
		$target = $this->create($target);

		$this->source = $this->emFactory->getInstanceEntityManager($source->getToken());
		$this->target = $this->emFactory->getInstanceEntityManager($target->getToken());

		$pdoSource = $this->source->getConnection()->getWrappedConnection();
		$pdoTarget = $this->source->getConnection()->getWrappedConnection();

		$tables = array_map(function(array $row){
			return $row[0];
		}, $pdoSource->query('SHOW TABLES;')->fetchAll(\PDO::FETCH_NUM));

		$sourceDbName = $this->dbPrefix . $source->getToken();
		$targetDbName = $this->dbPrefix . $target->getToken();

		$exclude = [
			'formadmin',
			'group_user',
			'message',
			'migration',
			'submit',
			'submit_group',
			'unread',
			'user',
			'usersubmitinstance',
			'value',
		];

		$pdoTarget->query('SET FOREIGN_KEY_CHECKS=0;')->execute();
		foreach($tables as $table){
			try{
				if(in_array($table, $exclude)){
					continue;
				}
				$sql = sprintf('INSERT INTO `%s`.`%s` SELECT * FROM `%s`.`%s`;', $targetDbName, $table, $sourceDbName, $table);
				$pdoTarget->prepare($sql)->execute();
			} catch(\Throwable $e){
				throw new \ErrorException(
					$e->getMessage() . sprintf(' #%s (%s -> %s, table: %s)', $e->getCode(), $sourceDbName, $targetDbName, $table),
					null,
					null,
					$e->getFile(),
					$e->getLine(),
					$e
				);
			}
		}

		$pdoTarget->prepare(
			sprintf('UPDATE `%s`.`media` SET instance_token = "%s" WHERE master_id IS NULL AND instance_token IS NULL;', $targetDbName, $source->getToken())
		)->execute();

		foreach(glob(APP_DIR_ROOT . sprintf('/web/shared/user/%s/attachments/*', $source->getToken())) as $file){
			$basename = basename($file);
			\copy(
				APP_DIR_ROOT . sprintf('/web/shared/user/%s/attachments/%s', $source->getToken(), $basename),
				APP_DIR_ROOT . sprintf('/web/shared/user/%s/attachments/%s', $target->getToken(), $basename)
			);
		}

		$pdoTarget->query('SET FOREIGN_KEY_CHECKS=1;')->execute();

		return true;
	}

}
