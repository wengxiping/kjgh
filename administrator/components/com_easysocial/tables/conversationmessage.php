<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableConversationMessage extends SocialTable
{
	public $id = null;
	public $conversation_id = null;
	public $type = null;
	public $message = null;
	public $created = null;
	public $created_by = null;

	// These columns are not real columns in the database table.
	protected $target = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_conversations_message', 'id' , $db);
	}

	/**
	 * Retrieves the author of the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCreator()
	{
		static $nodes = array();

		if (!isset($nodes[$this->created_by])) {
			$nodes[$this->created_by] = ES::user($this->created_by);
		}

		return $nodes[$this->created_by];
	}

	/**
	 * Retrieves the content of the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getContents()
	{
		if ($this->type == 'join') {
			return JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_INVITED_INTO_CONVERSATION_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'leave') {
			return JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_LEFT_CONVERSATION_MESSAGE', $this->getCreator()->getName());
		}

		if ($this->type == 'delete') {
			return JText::sprintf('COM_ES_CONVERSATIONS_DELETED_PARTICIPANT_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'message') {
			$message = $this->message;

			$tags = $this->getTags();

			// Apply mentions and hashtags
			if ($tags) {
				$message = ES::string()->processTags($tags, $message);
			}

			// Apply e-mail replacements
			$message = ES::string()->replaceEmails($message);

			// Apply hyperlinks
			$message = ES::string()->replaceHyperlinks($message);

			// Apply bbcode
			$message = ES::string()->parseBBCode($message, array('escape' => false, 'links' => false, 'code' => true));
		}

		return $message;
	}

	/**
	 * Retrieves a list of tags for this conversation
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTags()
	{
		$model = ES::model('Tags');

		$tags = $model->getTags($this->id, 'conversations');

		return $tags;
	}

	/**
	 * Retrieves the intro text portion of a message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIntro($overrideLength = null)
	{
		$config = ES::config();

		// Get the maximum length.
		$maxLength = is_null($overrideLength) ? $config->get('conversations.layout.intro') : $overrideLength;

		$message = $this->message;

		if ($this->type == 'join') {
			$message = JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_INVITED_INTO_CONVERSATION_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'leave') {
			$message = JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_LEFT_CONVERSATION_MESSAGE', $this->getCreator()->getName());
		}

		$message = strip_tags($message);
		$message = ES::string()->processEmoWithTruncate($message, $maxLength);

		return $message;
	}

	/**
	 * Retrieves a list of attachment for this conversation message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAttachments()
	{
		$model = ES::model('Files');

		$files = $model->getFiles($this->id, SOCIAL_TYPE_CONVERSATIONS);

		return $files;
	}


	/**
	 * Binds any temporary files to the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bindTemporaryFiles($ids, $deleteSource = true)
	{
		// This should only be executed with a valid conversation.
		if (!$this->id) {
			$this->setError(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_STORE_CONVERSATION_FIRST'));
			return false;
		}

		// Ensure that they are in an array form.
		$ids = ES::makeArray($ids);

		foreach ($ids as $id) {
			$file = ES::table('File');

			$file->sub = ES_FILE_SUB_PREFIX_CONVERSATION . $this->conversation_id;
			$file->uid = $this->id;
			$file->type = SOCIAL_TYPE_CONVERSATIONS;

			// Copy some of the data from the temporary table.
			$file->copyFromTemporary($id, $deleteSource);

			$file->store();
		}

		return true;
	}

	public function getType()
	{
		return strtolower($this->type);
	}

	/**
	 * This is only used when the conversation type is a "join" or "leave" type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTarget()
	{
		$target = $this->message;

		$user = ES::user($target);

		return $user;
	}

	/**
	 * Override parent's store method so that we can
	 * run our own maintenance here.
	 */
	public function store($updateNulls = false)
	{
		$state = parent::store($updateNulls);

		if ($state) {

			// Add a new location item if address, latitude , longitude is provided.
			$latitude = JRequest::getVar('latitude');
			$longitude = JRequest::getVar('longitude');
			$address = JRequest::getVar('address');

			// Let's add the location now.
			if (!empty($latitude ) && !empty($longitude) && !empty($address)) {
				$location = ES::table('Location');
				$location->latitude	= $latitude;
				$location->longitude = $longitude;
				$location->address = $address;
				$location->uid = $this->id;
				$location->type = SOCIAL_TYPE_CONVERSATIONS;
				$location->user_id = $this->created_by;

				$location->store();
			}
		}
		return $state;
	}

	/**
	 * Returns a standard location object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLocation()
	{
		$location = ES::table('Location');
		$state = $location->loadByType($this->id, SOCIAL_TYPE_CONVERSATIONS);

		if (!$state) {
			return false;
		}

		return $location;
	}

	/**
	 * Retrieves the last replied date
	 *
	 * @since	1.5
	 * @access	public
	 */
	public function getRepliedDate($lapsed = true)
	{
		$date = ES::date($this->created);

		if ($lapsed) {
			return $date->toLapsed();
		}

		return $date;
	}

}
