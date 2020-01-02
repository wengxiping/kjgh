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

class plgPayplansTosAjax extends PayPlans
{
	/**
	 * Renders the dialog for terms and conditions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function show()
	{
		$appId = $this->input->get('appId', 0, 'int');

		if (!$appId) {
			throw new Exception('Not allowed');
		}

		$app = PP::app()->getAppInstance($appId);

		if ($app->type != 'tos') {
			throw new Exception('Not allowed');
		}
		
		$type = $app->getAppParam('filter');
		$contents = $app->getAppParam('custom_content');
		$link = '';

		$namespace = 'plugins:/payplans/tos/dialogs/custom';

		// Joomla articles
		if ($type != 'custom_content') {
			$namespace = 'plugins:/payplans/tos/dialogs/article';

			// To resolve issue with SH404 component & Use @JRoute due to com_content router issue regarding view
			$id = (int) $app->getAppParam('joomla_article');

			$link = 'index.php?option=com_content&view=article&id=' . $id . '&tmpl=component';
			$link = rtrim(JURI::root(), '/') . '/' . $link;
			// // Ensure that the url is absolute
			// if (stristr($link, JURI::root()) === false) {
			// 	$link = rtrim(JURI::root(), '/') . $link;
			// }
		}

		$theme = PP::themes();
		$theme->set('contents', $contents);
		$theme->set('link', $link);
		$contents = $theme->output($namespace);

		$ajax = PP::ajax();
		return $ajax->resolve($contents);
	}
}
