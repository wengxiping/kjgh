<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class PayplansViewSubscription extends PayPlansAdminView
{
	/**
	 * Renders browser to search for subscription
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '');

		$this->set('callback', $callback);

		$output = parent::display('admin/subscription/dialogs/browse');

		return $this->resolve($output);
	}

	/**
	 * Renders the legend for subscription status
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getSubscriptionLegend()
	{
		$theme = PP::themes();
		$output = $theme->output('admin/subscription/dialogs/legend');

		return $this->resolve($output);
	}

	/**
	 * Renders the extend form for subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function extend()
	{
		$ids = $this->input->get('cid', array(), 'int');

		$theme = PP::themes();
		$theme->set('ids', $ids);
		$output = $theme->output('admin/subscription/dialogs/extend');

		return $this->resolve($output);
	}

	/**
	 * Renders the update status dialog for subscriptions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function updateStatus()
	{
		$ids = $this->input->get('cid', array(), 'int');

		$theme = PP::themes();
		$theme->set('ids', $ids);
		$output = $theme->output('admin/subscription/dialogs/update.status');
		
		return $this->resolve($output);		
	}

	/**
	 * Renders the Cancel Subscription form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmCancel()
	{
		$id = $this->input->get('id', 0, 'int');
		$order = PP::order($id);
		
		$this->set('order', $order);
		$output = $this->display('admin/subscription/dialogs/cancel.subscription');

		return $this->resolve($output);
	}

	/**
	 * Renders the Confirm Add Invoice form
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function confirmAddInvoice()
	{
		$id = $this->input->get('id', 0, 'int');
		$order = PP::order($id);
		
		$this->set('order', $order);
		$output = $this->display('admin/subscription/dialogs/confirm.addinvoice');

		return $this->resolve($output);
	}
}
