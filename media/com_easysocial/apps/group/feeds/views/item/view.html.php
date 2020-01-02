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

class FeedsViewItem extends SocialAppsView
{
	/**
	 * Renders the list of feeds from a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($uid = null, $docType = null )
	{
		$rssid = $this->input->get('rssid', 0, 'int');

		$group = ES::group($uid);

		if (!$group->canAccessFeeds()) {
			ES::raiseError(404, JText::_('COM_ES_FEEDS_DISABLED'));
		}

		$this->setTitle('APP_FEEDS_APP_TITLE');

		$params = $this->app->getParams();
		$limit 	= $params->get('total', 5);

		$model = FD::model('RSS');
		$item = $model->getItem($rssid);

		$parser = $model->getParser($item->url);

		if ($parser) {
			$item->parser = $parser;
			$item->total = @$parser->get_item_quantity();
			$item->items = @$parser->get_items(0, $limit);
		}

		$backLink = $group->getAppsPermalink($this->app->getAlias());

		$this->set('totalDisplayed', $limit);
		$this->set('appId', $this->app->id);
		$this->set('feed', $item);
		$this->set('cluster', $group);
		$this->set('user', $this->my);
		$this->set('backLink', $backLink);

		echo parent::display('themes:/site/feeds/item/default');
	}
}
