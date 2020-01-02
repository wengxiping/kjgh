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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Import SocialTable.
ES::import('admin:/tables/table');

class SocialTableNotification extends SocialTable
{
	/**
	 * The unique id which is auto incremented.
	 * @var int
	 */
	public $id = null;

	/**
	 * The unique id or target of the item
	 * @var int
	 */
	public $uid = null;

	/**
	 * The unique type of the item
	 * @var string
	 */
	public $type = null;

	/**
	 * The context type of the item
	 * @var string
	 */
	public $context_ids = null;

	/**
	 * The context type of the item
	 * @var string
	 */
	public $context_type = null;

	/**
	 * The unique command type of the item.
	 * @var string
	 */
	public $cmd = null;

	/**
	 * The application id from `#__social_apps`
	 * @var int
	 */
	public $app_id = null;

	/**
	 * The title that will be shown to the user.
	 * @var datetime
	 */
	public $title = null;

	/**
	 * The content that will be shown to the user.
	 * @var string
	 */
	public $content = null;

	/**
	 * Stores the url to the image. Only urls are allowed here. (Optional)
	 * @var string
	 */
	public $image = null;

	/**
	 * The datetime the notification item was created.
	 * @var datetime
	 */
	public $created = null;

	/**
	 * The state of the notification, 2 - hidden , 1 - read , 0 - unread.
	 * @var int
	 */
	public $state = null;

	/**
	 * The actor that generates this notification item.
	 * @var int
	 */
	public $actor_id = null;

	/**
	 * The actor type tht generates this notification item.
	 * @var int
	 */
	public $actor_type = null;

	/**
	 * The owner of the notification item.
	 * @var int
	 */
	public $target_id = null;

	/**
	 * The owner type of the notification item.
	 * @var int
	 */
	public $target_type = null;

	/**
	 * Additional params for this notification item.
	 * @var string
	 */
	public $params = null;

	/**
	 * The absolute uri to the unique item.
	 * @var string
	 */
	public $url = null;

	/**
	 * Determines if this is a broadcast item
	 * @var bool
	 */
	public $broadcast = null;

	/**
	 * The alias of this item.
	 * @var bool
	 */
	public $alias = null;

	public function __construct( &$db )
	{
		parent::__construct( '#__social_notifications' , 'id' , $db );
	}

	/**
	 * Marks a notification item as read
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function markAsRead()
	{
		$this->state	= SOCIAL_NOTIFICATION_STATE_READ;

		return $this->store();
	}

	/**
	 * Marks a notification item as unread
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function markAsUnread()
	{
		$this->state	= SOCIAL_NOTIFICATION_STATE_UNREAD;

		return $this->store();
	}

	/**
	 * Marks a notification item as read
	 *
	 * @since	1.0
	 * @access	public
	 * @return	bool		True on success, false otherwise.
	 */
	public function markAsHidden()
	{
		$this->state	= SOCIAL_NOTIFICATION_STATE_HIDDEN;

		return $this->store();
	}

	/**
	 * Retrieves the actor of the notification item
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getActor()
	{
		if (!$this->actor_id) {
			return false;
		}
		
		$user = FD::user($this->actor_id);

		return $user;
	}

	/**
	 * Retrieves the permalink for a notification item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true)
	{
		$link = ESR::notifications(array('id' => $this->id, 'layout' => 'route'), $xhtml);


		return $link;
	}

	/**
	 * Get's the actor's avatar.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getActorAvatar( $size = SOCIAL_AVATAR_MEDIUM )
	{
		$avatar 	= FD::Table( 'Avatar' );
		$avatar->load( array( 'uid' => $this->actor_id , 'type' => $this->actor_type ) );

		return $avatar->getSource( $size );
	}

	/**
	 * Returns the parameters of this notification item in SocialRegistry format.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getParams()
	{
		return FD::registry($this->params);
	}

	/**
	 * Allow caller to set a custom actor alias
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 */
	public function setActorAlias($object)
	{
		$this->alias = $object;
	}

	/**
	 * Retrieves the actor of the notification
	 *
	 * @since	1.3.8
	 * @access	public
	 */
	public function getActorAlias()
	{
		if (!$this->alias) {
			return $this->getActor();
		}

		return $this->alias;
	}
}
