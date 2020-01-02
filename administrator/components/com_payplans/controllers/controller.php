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
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Checks if the current viewer can really access this section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function checkAccess($rule)
	{
		$rule = 'payplans.' . $rule;

		if (!$this->my->authorise($rule , 'com_payplans')) {
			PP::info()->set(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return $this->app->redirect('index.php?option=com_payplans');
		}
	}

	/**
	 * Processes all view requests
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$type = $this->doc->getType();
		$name = $this->input->get('view', 'dashboard', 'cmd');
		$layout = $this->input->get('layout', 'default', 'cmd');

		$view = $this->getView($name, $type, '');

		$view->setLayout($layout);

		// Before rendering the output, trigger plugins
		$args = array(&$view, &$task);

		$pluginResult = PPEvent::trigger('onPayplansViewBeforeRender', $args, '', $this);
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
}
