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

class EasySocialViewRepost extends EasySocialSiteView
{
	/**
	 * Returns an ajax chain.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function share($uid = null, $element = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = 0)
	{
		$share = ES::get('Repost', $uid, $element, $group);
		$count = $share->getCount();

		$countPluralize = ES::language()->pluralize($count, true)->getString();
		$text = JText::sprintf('COM_EASYSOCIAL_REPOST' . $countPluralize, $count);

		$hidden = ($count > 0) ? false : true;
		$html = '';

		if ($streamId) {
			$stream = ES::stream();
			$stream->getItem($streamId);

			$html = $stream->html();
		}

		return $this->ajax->resolve($text, $hidden, $count, $html);
	}

	/**
	 * Post process after retrieving a list of reposters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRepostAuthors($users)
	{
		$theme = ES::themes();
		$theme->set('users', $users);

		$contents = $theme->output('site/repost/popbox/authors');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the repost form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form()
	{
		ES::requireLogin();

		$element = $this->input->get('element', '', 'cmd');
		$group = $this->input->get('group', '', 'cmd');
		$uid = $this->input->get('id', 0, 'int');
		$streamId = $this->input->get('streamId', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'word');

		$shareAs = 'user';

		$repost = ES::repost($uid, $element, $group);

		if ($clusterId && $clusterType) {
			$repost->setCluster($clusterId, $clusterType);
			$cluster = $repost->getCluster();

			// Only Page admin can repost as Page
			if ($cluster->getType() == SOCIAL_TYPE_PAGE && $cluster->isAdmin()) {
				$shareAs = $this->input->get('shareAs', 'page', 'word');
			}
		}

		if ($streamId) {
			$repost->setStreamId($streamId);
		}

		$theme = ES::themes();

		// Check if the current user already shared this item or not. if yes, display a message and abort the sharing process.
		if ($repost->isShared($this->my->id, $shareAs)) {
			$html = $theme->output('site/repost/dialogs/message');
			return $this->ajax->resolve($html);
		}

		// Get dialog
		$preview = $repost->preview();
		$theme->set('preview', $preview);
		$theme->set('shareAs', $shareAs);
		$html = $theme->output('site/repost/dialogs/form');

		return $this->ajax->resolve($html);
	}
}
