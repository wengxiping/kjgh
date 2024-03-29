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

class SocialSidebarVideos extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$layout = $this->input->get('layout', '', 'cmd');

		// We do not want to render anything on the item layout
		if ($layout == 'item') {
			return;
		}

		return $this->renderListing();
	}

	public function renderListing()
	{
		$helper = ES::viewHelper('Videos', 'List');

		$filter = $helper->getCurrentFilter();

		$uid = $helper->getUid();
		$type = $helper->getType();
		$adapter = $helper->getAdapter($uid, $type);

		$cluster = $helper->getCluster();
		$titles = $helper->getPageTitles();

		$createLink = $helper->getCreateLink();
		$allowCreation = $helper->canCreateVideo();

		$counters = $helper->getCounters();

		$browseView = $helper->isBrowseView();
		$isCluster = $helper->isCluster();
		$isUserProfileView = $helper->isUserProfileView();

		// Custom filters
		$customFilters = $helper->getCustomFilters();
		$canCreateFilter = $helper->canCreateFilter();
		$createCustomFilterLink = $helper->getCreateCustomFilterLink();
		$activeCustomFilter = $helper->getActiveCustomFilter();

		// Filter acl
		$filtersAcl = $helper->getFiltersAcl();

		// Get a list of video categories on the site
		$categories = $helper->getCategories();
		$activeCategory = $helper->getActiveCategory();

		$path = $this->getTemplatePath('videos');

		require($path);
	}
}
