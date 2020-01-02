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

class PayplansViewRewriter extends PayPlansAdminView
{
	/**
	 * Displays rewriter tokens
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function view()
	{
		$args = array();
		$apps = PP::event()->trigger('onPayplansRewriterDisplayTokens', $args);

		$rewriter = PP::rewriter();
		$rewriter->mapConfig();
		$rewriter->setMapping(PP::plan(), false);
		$rewriter->setMapping(PP::subscription(), false);
		$rewriter->setMapping(PP::invoice(), false);
		$rewriter->setMapping(PP::transaction(), false);
		$rewriter->setMapping(PP::user(), false);

		$items = array(
			'CONFIG' => array(),
			'PLAN' => array(),
			'SUBSCRIPTION' => array(),
			'INVOICE' => array(),
			'TRANSACTION' => array(),
			'USER' => array()
		);

		$keys = array_keys($items);

		foreach ($rewriter->mapping as $key => $value) {
			$parts = explode('_', $key);
			$index = $parts[0];

			$items[$index][] = $key;
		}

		$theme = PP::themes();
		$theme->set('rewriter', $rewriter);
		$theme->set('items', $items);
		$theme->set('apps', $apps);
		$output = $theme->output('admin/rewriter/dialogs/view');

		return $this->ajax->resolve($output);
	}
}