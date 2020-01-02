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

class FeedsViewCanvas extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		$user = ES::user($userId);
		$params = $this->getUserParams($user->id);

		// Get the app params
		$appParams = $this->app->getParams();

		$limit = $params->get('total', $appParams->get('total', 5));

		$id = $this->input->get('cid', 0, 'int');

		$feed = $this->getTable('Feed');
		$feed->load($id);
	
		$parser = $feed->getParser();
		$feed->total = @$parser->get_item_quantity();
		$feed->items = @$parser->get_items(0, $limit);

		$this->setTitle($feed->title);

		$backLink = $this->app->getUserPermalink($user->getAlias());
		
		$this->set('backLink', $backLink);
		$this->set('feed', $feed);
		$this->set('user', $user);
		$this->set('totalDisplayed', $limit);
		$this->set('params', $params);

		echo parent::display('item/default');
	}
}
