<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialSharesHelperStream extends SocialSharesHelper
{
	/**
	 * Gets the content of the repost
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent()
	{
		// Source of repost
		$sourceActor = $this->getSourceActor();

		// Exclude site admin stream repost items
		if ($this->config->get('stream.exclude.admin') && !ES::user()->isSiteAdmin() && $sourceActor->isSiteAdmin()) {
			return $this->restricted();
		}

		$message = $this->formatContent($this->share->content);

		$stream = ES::stream();
		$data = $stream->getItem($this->share->uid, '', '', false, array('perspective' => 'dashboard', 'disableActions' => true));

		if ($data === true || !$data) {
			return $this->restricted();
		}

		// Get the content
		$data = $data[0];
		$content = $data->content;
		$preview = $data->preview;

		$content = $content .  $data->getMetaHtml();

		$theme = ES::themes();

		$theme->set('sourceActor', $sourceActor);
		$theme->set('message', $message);
		$theme->set('content', $content);
		$theme->set('preview', $preview);

		$html = $theme->output('themes:/site/streams/repost/stream/preview');

		return $html;
	}

	/**
	 * Generates the unique link id for the original reposted item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLink($sef = true)
	{
		$link = ESR::stream(array('layout' => 'item', 'id' => $this->item->contextId, 'sef' => $sef));

		return $link;
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
			// Load the stream
			$stream = ES::table('Stream');
			$stream->load($this->share->uid);

			$items[$this->share->uid] = $stream;
		}

		return $items[$this->share->uid];
	}

	/**
	 * Retrieves the source text
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSourceActor()
	{
		$stream = $this->getSource();

		$actor = ES::user($stream->actor_id);

		return $actor;
	}

	/**
	 * Get the stream title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamTitle()
	{
		// Load the stream
		$stream = $this->getSource();

		if (!$stream->id) {
			return '';
		}

		$link = ESR::stream(array('layout' => 'item', 'id' => $this->share->uid));

		// Get the target user.
		$target = ES::user($stream->actor_id);
		$actor = $this->item->actors[0];

		$theme = ES::themes();
		$theme->set('actor', $actor);
		$theme->set('link', $link);
		$theme->set('target', $target);

		$title = $theme->output('themes:/site/streams/repost/stream/title');

		return $title;
	}


}
