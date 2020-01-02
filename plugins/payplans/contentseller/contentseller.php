<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class plgPayplansContentseller extends PPPlugins
{
	protected $_resource = 'contentseller.article';

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Check whether the plugin should process or not
		if (JString::strpos($article->text, 'pp-contentseller') === false) {
			return true;
		}

		$helper = $this->getAppHelper();

		return $helper->processArticle($article);
	}

	/**
	 * Trigger before saving the subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionBeforeSave($prev, PPSubscription $new)
	{
		$helper = $this->getAppHelper();
		$helper->processBeforeSubscriptionSave($prev, $new);

		return true;
	}

	/**
	 * Trigger after the susbcriptions is saved
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function onPayplansSubscriptionAfterSave($prev, PPSubscription $new)
	{
		$helper = $this->getAppHelper();
		$helper->processAfterSubscriptionSave($prev, $new);

		return true;
	}
}
