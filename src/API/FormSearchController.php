<?php

namespace Visitares\API;

use Visitares\Storage\Facade\{InstanceStorageFacade, SystemStorageFacade};

class FormSearchController{

  /**
   * @var InstanceStorageFacade
   */
  private $storage;

  /**
   * @var \Doctrine\DBAL\Driver\PDO\Connection
   */
  private $pdo;

  /**
   * @param InstanceStorageFacade $storage
   * @param SystemStorageFacade $systemStorage
   * @param string $token
   */
  public function __construct(
    InstanceStorageFacade $storage,
    SystemStorageFacade $systemStorage,
    string $token
  ){
    $this->storage = $storage;
		$this->storage->setToken($token);
    $this->pdo = $storage->getEntityManager()->getConnection()->getWrappedConnection();
    $this->instance = $systemStorage->instance->findByToken($token);
  }

  /**
   * @param int $uid user id
   * @param string $search
   * @param string $lang language
   * @return array
   */
  public function search(int $uid, string $search, string $lang = 'de'){
    $rows = $this->pdo
      ->query(sprintf('SELECT group_id AS id FROM group_user WHERE user_id = %d', $uid))
      ->fetchAllAssociative();
    
    $rows = array_map(fn($arr) => (object)$arr, $rows);

    $groupIds = array_map(function($row){
      return intval($row->id);
    }, $rows);
    
    if(!$groupIds){
      return [];
    }

    $fields = ['t_form_name.content AS FormTitle'];
    $joins = [
      'LEFT JOIN
        translated t_form_name ON t_form_name.translation_id = f.nameTranslation_id AND t_form_name.language_id = l.id'
    ];
    $conditions = [
      't_form_name.content LIKE :Search'
    ];

    if($this->instance->getShowFormSearchDescription()){
      $fields[] = 't_form_desc.content AS FormDescription';
      $joins[] = 'LEFT JOIN translated t_form_desc ON t_form_desc.translation_id = f.descriptionTranslation_id AND t_form_desc.language_id = l.id';
      $conditions[] = 't_form_desc.content LIKE :Search';
    }

    if($this->instance->getShowFormSearchShortDescription()){
      $fields[] = 't_form_sdesc.content AS FormShortDescription';
      $joins[] = 'LEFT JOIN translated t_form_sdesc ON t_form_sdesc.translation_id = f.shortDescriptionTranslation_id AND t_form_sdesc.language_id = l.id';
      $conditions[] = 't_form_sdesc.content LIKE :Search';
    }

    $sql = sprintf('
      SELECT
        f.id AS FormId,
        f.type AS FormType,
        IF(f.modificationDate IS NULL, f.creationDate, f.modificationDate) AS FormDate,
        section.id AS SectionId,
        campaign.id AS CampaignId,
        t_section_name.content AS SectionName,
        t_campaign_name.content AS CampaignName,
        %s
      FROM
        form f
      LEFT JOIN
        category campaign ON campaign.id = f.category_id
      LEFT JOIN
        category_group campaign_group ON campaign_group.category_id = campaign.id
      LEFT JOIN
        client section ON section.id = campaign.client_id
      LEFT JOIN
        language l ON l.code = :LanguageCode
      %s
      LEFT JOIN
        translated t_campaign_name ON t_campaign_name.translation_id = campaign.nameTranslation_id AND t_campaign_name.language_id = l.id
      LEFT JOIN
        translated t_section_name ON t_section_name.translation_id = section.nameTranslation_id AND t_section_name.language_id = l.id
      WHERE
            section.isActive
        AND campaign.isActive
        AND f.isActive
        AND f.type != 8
        AND campaign_group.group_id IN (:GroupIds)
        AND (
              %s
          OR  t_campaign_name.content LIKE :Search
        )
      GROUP BY
        f.id
      ORDER BY
        t_form_name.content
    ',
      implode(', ', $fields),
      implode(' ', $joins),
      implode(' OR ', $conditions)
    );

    $sql = str_replace(':GroupIds', implode(', ', $groupIds), $sql);

    $stmt = $this->pdo->prepare($sql);
    $res = $stmt->execute([
      ':Search' => sprintf('%%%s%%', $search),
      ':LanguageCode' => $lang,
    ]);

    $rows = $res->fetchAllAssociative();
    $rows = array_map(fn($arr) => (object)$arr, $rows);

    return array_map(function($row){
      $row->FormId = intval($row->FormId);
      $row->SectionId = intval($row->SectionId);
      $row->CampaignId = intval($row->CampaignId);
      return $row;
    }, $rows);
  }

}