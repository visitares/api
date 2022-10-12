<?php

namespace Visitares\API;

use DateTime;

use Doctrine\ORM\Query;

use Visitares\Entity\Form;
use Visitares\Entity\FormAdmin;
use Visitares\Entity\Message;
use Visitares\Entity\Submit;
use Visitares\Entity\Unread;
use Visitares\Entity\Factory\AttachmentFactory;
use Visitares\Service\Database\DatabaseFacade;
use Visitares\Storage\Facade\SystemStorageFacade;
use Visitares\Storage\Facade\InstanceStorageFacade;

use Visitares\Service\FormAdminService;

/**
 * @author Ricard Derheim <rderheim@dereim-software.de>
 */
class MessageController{
	/**
	 * @var DatabaseFacade
	 */
	protected $db = null;

	/**
	 * @var SystemStorageFacade
	 */
	protected $systemStorage = null;

	/**
	 * @var InstanceStorageFacade
	 */
	protected $storage = null;

	/**
	 * @var string
	 */
	protected $token = null;

	/**
	 * @var FormAdminService
	 */
	protected $formAdminService = null;

	/**
	 * @param DatabaseFacade $database
	 * @param SystemStorageFacade $systemStorage
	 * @param InstanceStorageFacade $instanceStorage
	 * @param string $token
	 * @param FormAdminService      $formAdminService
	 */
	public function __construct(
		DatabaseFacade $database,
		SystemStorageFacade $systemStorage,
		InstanceStorageFacade $storage,
		$token,
		FormAdminService $formAdminService
	){
		$this->db = $database;
		$this->systemStorage = $systemStorage;
		$this->storage = $storage;
		if($this->instance = $this->systemStorage->instance->findByToken($token)){
			$this->storage->setToken($token);
		}
		$this->formAdminService = $formAdminService;
		$this->formAdminService->setEntityManager($this->storage->getEntityManager());
	}

	/**
	 * @param  integer $id
	 * @return Message[]
	 */
	public function getByUser($id){
		session_write_close();

		$dql = sprintf('
			SELECT DISTINCT
				s,
				m.id as MessageId
			FROM
				Visitares\Entity\Submit s
			LEFT JOIN
				s.form f
			LEFT JOIN
				f.category c
			LEFT JOIN
				c.groups g
			LEFT JOIN
				g.users u

			INNER JOIN
				s.messages m
			LEFT JOIN
				s.messages m2
			WITH
				s = m2.submit
			AND	m.id < m2.id

			WHERE
				m2.id IS NULL
			AND	f.type = 3
			AND f.publicStats = true
			AND	u.id = :user
			AND s.creationDate >= DATE_SUB(CURRENT_DATE(), %d, \'day\')

			ORDER BY
				m.creationDate DESC
		', $this->instance->getStatsDayRange());

		$em = $this->storage->getEntityManager();
		$query = $em->createQuery($dql);
		$query->setParameter('user', $id);
		$rows = $query->getResult();

		$currentUser = $em->getRepository('Visitares\Entity\User')->findOneById($id);

		$result = [];
		foreach($rows as $row){
			$submit = $row[0];

			$firstMessage = $em->getRepository('Visitares\Entity\Message')->findOneBy([
				'submit' => $submit
			], [
				'submit' => 'asc'
			]);
			$message = $em->getRepository('Visitares\Entity\Message')->findOneById($row['MessageId']);
			$user = $message->getUser();

			$unread = $em->getRepository('Visitares\Entity\Unread')->findOneBy([
				'user' => $currentUser,
				'submit' => $submit
			]);

			$fullname = null;
			if($user){
				$fullname = implode(' ', [
					$user->getFirstname(),
					$user->getLastname()
				]);
			}
			$result[] = [
				'category' => $submit->getForm()->getCategory()->getId(),
				'submit' => $submit->getId(),
				'form' => $submit->getForm()->getNameTranslation(),
				'formId' => $submit->getForm()->getId(),
				'messageAdmins' => array_map(function($item){
					return $item->getUser()->getId();
				}, $this->formAdminService->getAdmins($submit->getForm(), FormAdmin::ROLE_MESSAGE_ADMIN)),
				'message' => $message->getMessage(),
				'messageId' => $message->getId(),
				'published' => $firstMessage->getPublished(),
				'fullname' => $fullname,
				'date' => $message->getCreationDate()->format('Y-m-d H:i:s'),
				'unread' => $unread ? [
					'id' => $unread->getId(),
					'count' => $unread->getCount()
				] : null
			];
		}

		return $result;
	}

	/**
	 * @param  integer $submitId
	 * @return array
	 */
	public function getBySubmit($submitId){
		session_write_close();
		
		$em = $this->storage->getEntityManager();
		$submits = $em->getRepository('Visitares\Entity\Submit');
		if($submit = $submits->findOneById($submitId)){
			$messages = $em->getRepository('Visitares\Entity\Message');
			$messages = $messages->findBy([
				'submit' => $submit->getId()
			]);

			$result = [];
			foreach($messages as $message){
				$author = null;
				if($user = $message->getUser()){
					$author = implode(' ', [
						$user->getFirstname(),
						$user->getLastname()
					]);
				}

				$result[] = [
					'submit' => $submit->getId(),
					'date' => $message->getCreationDate()->format('Y-m-d H:i:s'),
					'message' => $message->getMessage(),
					'messageId' => $message->getId(),
					'published' => $message->getPublished(),
					'author' => $author,
					'user' => $user ? $user->getId() : null,
					'attachments' => $message->getAttachments()->toArray()
				];
			}

			return $result;
		}
		return [];
	}

	/**
	 * @param  integer $submitId
	 * @param  array   $data
	 * @return Message
	 */
	public function reply($submitId, $data){

		$em = $this->storage->getEntityManager();
		$submits = $em->getRepository('Visitares\Entity\Submit');
		if($submit = $submits->findOneById($submitId)){
			// Check for dirty words
			$words = $this->storage->dirtyWord->findAll();
			$code = $submit->getLanguage()->getCode();
			$match = strtolower($data['message']);
			foreach($words as $word){
				if(strpos($match, strtolower($word->getWord($code))) !== false){
					return[
						'error' => true,
						'code' => 'MESSAGE_CONTAINS_DIRTY_WORDS'
					];
				}
			}

			$message = new Message;
			$user = null;
			if(isset($data['user']) && $data['user']){
				$user = $this->storage->user->findById($data['user']);
				$message->setUser($user);
			}
			$message->setSubmit($submit);
			$message->setMessage($data['message']);
			$this->storage->store($message);
			$this->storage->apply();

			// Create attachments
			$attachments = [];
			if(isset($data['attachments'])){
				$attachmentFactory = new AttachmentFactory;
				foreach($data['attachments'] as $file){
					$attachment = $attachmentFactory->fromArray($file);
					$attachment->setMessage($message);
					$this->storage->store($attachment);
					$this->storage->apply();
					$attachments[] = $attachment;
				}
			}

			$author = null;
			if($user = $message->getUser()){
				$author = implode(' ', [
					$user->getFirstname(),
					$user->getLastname()
				]);
			}

			$this->markMessageAsUnread($user, $submit->getForm(), $submit, $message);

			return[
				'user' => $user ? $user->getId() : null,
				'date' => $message->getCreationDate()->format('Y-m-d H:i:s'),
				'message' => $message->getMessage(),
				'messageId' => $message->getId(),
				'author' => $author,
				'attachments' => $attachments
			];
		}
		return false;
	}

	/**
	 * @param  User    $user
	 * @param  Form    $form
	 * @param  Submit  $submit
	 * @param  Message $message
	 * @return void
	 */
	protected function markMessageAsUnread($author, Form $form, Submit $submit, Message $message){
		$dql = '
			SELECT
				u
			FROM
				Visitares\Entity\User u
			LEFT JOIN
				u.groups g
			LEFT JOIN
				g.categories c
			WHERE
				u.id != :author
			AND c.id = :category';

		$em = $this->storage->getEntityManager();
		$query = $em->createQuery($dql);
		$query->setParameter('author', $author ? $author->getId() : -1);
		$query->setParameter('category', $form->getCategory()->getId());
		$users = $query->getResult();

		$unreads = $em->getRepository('Visitares\Entity\Unread');

		foreach($users as $user){
			// Try to select an already existing row
			$unread = $unreads->findOneBy([
				'user' => $user->getId(),
				'submit' => $submit->getId()
			]);

			if($unread){
				// Update existing row
				$unread->incCount();
			} else{
				// Create new row
				$unread = new Unread;
				$unread->setUser($user);
				$unread->setSubmit($submit);
				$unread->setMessage($message);
				$this->storage->store($unread);
			}

			// Apply changes
			$this->storage->apply();
		}
	}

	/**
	 * @param  integer $userId
	 * @param  integer $unreadId
	 * @return boolean
	 */
	public function read($userId, $unreadId){
		$em = $this->storage->getEntityManager();
		$unreads = $em->getRepository('Visitares\Entity\Unread');
		if($unread = $unreads->findOneById($unreadId)){
			if($unread->getUser()->getId() === (int)$userId){
				$em->remove($unread);
				$em->flush();
				return true;
			}
		}
		return false;
	}

	/**
	 * @param  integer $id
	 * @return integer
	 */
	public function unread($id){
		if($user = $this->storage->user->findById($id)){
			$em = $this->storage->getEntityManager();
			$unreads = $em->getRepository('Visitares\Entity\Unread');
			$unreads = $unreads->findBy([
				'user' => $user->getId()
			]);
			$result = 0;
			foreach($unreads as $unread){
				$result += $unread->getCount();
			}
			return $result;
		}
		return null;
	}

	/**
	 * @param  integer $messageId
	 * @param  integer $attachmentId
	 * @return string
	 */
	public function getAttachmentData($id){
		$em = $this->storage->getEntityManager();
		$attachment = $em->getRepository('Visitares\Entity\Attachment')->findOneBy([
			'id' => $id
		]);
		if($attachment){
			header('Content-Type: ' . $attachment->getMimetype() . '; charset=utf-8');
			readfile($attachment->getData());
			exit;
		}
		return null;
	}

	/**
	 * @param integer $id
	 * @param boolean $published
	 */
	public function setMessagePublished($id, $published){
		$em = $this->storage->getEntityManager();
		$message = $em->getRepository('Visitares\Entity\Message')->findOneBy([
			'id' => $id
		]);
		if($message){
			$message->setPublished($published);
			$em->persist($message);
			$em->flush();
			return true;
		}
		return false;
	}
}