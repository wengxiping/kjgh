<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( 'JPATH_BASE' ) or die( 'Unauthorized Access' );

// Import main table
FD::import('admin:/tables/table');

/**
 * Object relation mapping for conversation.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialTableConversation extends SocialTable
{
	public $id = null;
	public $title = null;
	public $title_alias = null;
	public $isparticipant = null;
	public $notification = null;
	public $created = null;
	public $created_by = null;
	public $lastreplied = null;
	public $type = null;

	public $isread = null;
	public $message = null;

	public function __construct( $db )
	{
		parent::__construct('#__social_conversations', 'id' , $db);
	}

	/**
	 * Override's parent's store behavior
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return	bool		True on success false otherwise
	 */
	public function store($updateNulls = false)
	{
		// Determines if this is a new conversation object.
		$isNew = $this->id ? false : true;
		$state = parent::store();

		if ($isNew) {
			// @badge: conversation.create
			$badge 	= ES::badges();
			$badge->log('com_easysocial', 'conversation.create', $this->created_by, JText::_('COM_EASYSOCIAL_CONVERSATIONS_BADGE_STARTED_NEW_CONVERSATION'));

			// @points: conversation.create
			// Assign points when user starts new conversation
			$type 	= $this->type == SOCIAL_CONVERSATION_SINGLE ? '' : '.group';
			$points = ES::points();
			$points->assign( 'conversation.create' . $type , 'com_easysocial' , $this->created_by );
		}

		return $state;
	}

	/*
	 * Loads a conversation record based on the existing conversations.
	 *
	 * @param	int		$creator	The node id of the creator.
	 * @param	int		$recipient	The node id of the recipient.
	 */
	public function loadByRelation($creator, $recipient, $type)
	{
		$db = ES::db();
		$query	= 'SELECT COUNT(1) AS related,b.* FROM ' . $db->nameQuote( '#__social_conversations_participants' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( $this->_tbl ) . ' AS b '
				. 'ON b.' . $db->nameQuote( 'id' ) . ' = a.' . $db->nameQuote( 'conversation_id' ) . ' '
				. 'WHERE ( '
				. 'a.' . $db->nameQuote( 'user_id') . ' = ' . $db->Quote( $creator ) . ' '
				. 'OR '
				. 'a.' . $db->nameQuote( 'user_id' ) . ' = ' . $db->Quote( $recipient ) . ' '
				. ') '
				. 'AND b.' . $db->nameQuote( 'type' ) . ' = ' . $db->Quote( $type ) . ' '
				. 'GROUP BY a.' . $db->nameQuote( 'conversation_id' )
				. 'ORDER BY related DESC, b.`id` desc limit 1';

		$db->setQuery($query);

		$data = $db->loadObject();

		if (!isset($data->related)) {
			return false;
		}

		if ($data->related >= 2) {
			return parent::bind($data);
		}

		return false;
	}
}
