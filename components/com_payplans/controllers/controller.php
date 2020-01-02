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

PP::import('admin:/includes/controller');

class PayPlansController extends PayPlansControllerMain
{
	/**
	 * Processes all view requests
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$type = $this->doc->getType();
		$name = $this->input->get('view', 'plan', 'cmd');
		$layout = $this->input->get('layout', 'default', 'cmd');

		$view = $this->getView($name, $type, '');

		// Do not remove this.
		// This needs to fix backwards compatible url for PayPlans prior to 4.0
		// to support old IPN urls
		$this->fixLegacyLinks($name, $layout, $task);

		$view->setLayout($layout);

		// Before rendering the output, trigger plugins
		$args = array(&$view, &$task);

		$pluginResult = PPEvent::trigger('onPayplansViewBeforeExecute', $args, '', $this);
		$pluginResult = $view->formatPluginResult($pluginResult);

		$view->set('pluginResult', $pluginResult);

		if ($layout != 'default') {
			if (!method_exists($view, $layout)) {
				$view->display();
			} else {
				call_user_func_array(array($view, $layout), array());
			}

			return;
		}

		$view->display();

		return;
	}

	/**
	 * Fixes legacy query strings. Do not remove this method
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function fixLegacyLinks(&$name, &$layout, &$task)
	{
		$task = $this->input->get('task', '', 'cmd');

		// Remap of view=payment&task=notify for IPN
		if ($name == 'payment' && $task == 'notify') {
			$layout = 'notify';
		}

		// Remap of view=payment&task=cancel for IPN
		if ($name == 'payment' && $task == 'cancel') {
			$layout = 'cancel';
		}

		// Remap of view=payment&task=complete for success payment
		if ($name == 'payment' && $task == 'complete') {
			$layout = 'complete';
		}
	}
}
