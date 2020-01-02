<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

FD::import('admin:/tables/table');

class SocialTableStreamItem extends SocialTable
{
	public $id = null;
	public $actor_id = null;
	public $actor_type = null;
	public $context_type = null;
	public $context_id = null;
	public $verb = null;
	public $target_id = null;
	public $created = null;
	public $uid = null;
	public $sitewide = null;
	public $params = null;
	public $state = null;

	static $_streamitems 	= array();

	public function __construct( $db )
	{
		parent::__construct('#__social_stream_item', 'id', $db);
	}


	/**
	 * Overrides parent's load implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load( $keys = null, $reset = true )
	{
		$loadFromUid = false;

		if( is_array( $keys ) )
		{
			if( count( $keys ) == 1 && isset( $keys['uid'] ) )
			{
				$loadFromUid = true;
			}
			else
			{
				return parent::load( $keys, $reset );
			}
		}

		$state = false;
		if( $loadFromUid )
		{
			$uid = $keys['uid'];

			if(! isset( self::$_streamitems[ $uid ] ) )
			{
				$state = parent::load( array( 'uid' => $uid ) );
				self::$_streamitems[ $uid ] = $this;
				return $state;
			}

			$state = parent::bind( self::$_streamitems[ $uid ] );
		}
		else
		{
			$state = parent::load( $keys, $reset );
		}

		return $state;
	}

	public function loadByUIDBatch( $ids )
	{
		$db = FD::db();
		$sql = $db->sql();

		$streamIds = array();

		foreach( $ids as $pid )
		{
			if(! isset( self::$_streamitems[$pid] ) )
			{
				$streamIds[] = $pid;
			}
		}

		if( $streamIds )
		{
			foreach( $streamIds as $pid )
			{
				self::$_streamitems[$pid] = false;
			}

			$idSegments = array_chunk( $streamIds, 5 );
			//$idSegments = array_chunk( $streamIds, count($streamIds) );

			$query = '';
			for( $i = 0; $i < count( $idSegments ); $i++ )
			{
				$segment    = $idSegments[$i];

				$ids = implode( ',', $segment );

				$query .= 'select * from `#__social_stream_item` where `uid` IN ( ' . $ids . ')';

				if( ($i + 1)  < count( $idSegments ) )
				{
					$query .= ' UNION ';
				}

			}

			$sql->raw( $query );
			$db->setQuery( $sql );

			$results = $db->loadObjectList();

			if( $results )
			{
				foreach( $results as $row )
				{
					self::$_streamitems[$row->uid] = $row;
				}
			}
		}
	}



	/**
	 * Bind's the stream data
	 *
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bind( $data , $ignore = array() )
	{
		// Request parent to bind the data.
		$state 			= parent::bind( $data , $ignore );

		return $state;
	}

	/**
	 * Returns the stream's permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink( $xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		return FRoute::stream( array( 'id' => $this->uid , 'layout' => 'item', 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef) , $xhtml );
	}

	/**
	 * Checks if the provided user is allowed to hide this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hideable( $id = null )
	{
		$isOwner 	= $this->isOwner( $id );
		$isAdmin	= $this->isAdmin( $id );

		if( $isOwner || $isAdmin )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is allowed to delete this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteable($id = null)
	{
		$isOwner = $this->isOwner( $id );
		$isAdmin = $this->isAdmin( $id );

		if ($isOwner || $isAdmin) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isAdmin($id = null)
	{
		$user = ES::user($id);

		if ($user->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is the owner of this item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isOwner($id = null)
	{
		$user = ES::user($id);

		if ($this->actor_id == $user->id) {
			return true;
		}

		return false;
	}
}
