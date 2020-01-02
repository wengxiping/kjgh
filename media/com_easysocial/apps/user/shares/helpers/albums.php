<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSharesHelperAlbums extends SocialSharesHelper
{
	/**
	 * Gets the content of the repost
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent()
	{
		$message = $this->formatContent($this->share->content);

		// Load the album object
		$album = $this->getSource();

		// Determines if the current user is allowed to view
		$privacy = $this->my->getPrivacy();

		if (!$privacy->validate('albums.view', $album->id, SOCIAL_TYPE_ALBUM, $album->uid)) {
			return $this->restricted();
		}

		$theme = ES::themes();
		$theme->set('album', $album);
		$theme->set('message', $message);

		$preview = $theme->output('themes:/site/streams/repost/albums/preview');

		return $preview;
	}

	/**
	 * Gets the repost source message
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSource()
	{
		static $items = array();

		if (!isset($items[$this->share->uid])) {
			$album = ES::table('Album');
			$album->load($this->share->uid);

			$items[$this->share->uid] = $album;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Generates the unique link id for the original reposted item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLink($sef = true)
	{
		$link = ESR::albums(array('id' => $this->item->contextId, 'sef' => $sef));

		return $link;
	}

	/**
	 * Get the stream title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamTitle()
	{
		// Load the album
		$album = $this->getSource();
		$creator = ES::user($album->uid);

		// Since it may be aggregated
		$names = ES::string()->namesToStream($this->item->actors, true, 3);

		$theme = ES::themes();
		$theme->set('album', $album);
		$theme->set('creator', $creator);
		$theme->set('names', $names);

		$title = $theme->output('themes:/site/streams/repost/albums/title');

		return $title;
	}
}
