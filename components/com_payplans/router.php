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

/**
 * Proxy layer to support Joomla 4.0 and Joomla 3.0
 *
 * @since  4.0.2
 */
class PayplansRouterBase
{
	public static function buildRoute(&$query)
	{
		$segments = array();

		$active = JFactory::getApplication()->getMenu()->getActive();

		// if ItemId supplied, lets use it as active menu
		$itemId = isset($query['Itemid']) && $query['Itemid'] ? $query['Itemid'] : '';
		if ($itemId) {
			$active = JFactory::getApplication()->getMenu()->getItem($itemId);
		}

		$activeView = isset($active->query['view']) ? $active->query['view'] : '';
		$view = '';

		if (isset($query['view']) && $query['view']) {

			$view = $query['view'];

			if ($activeView != $view) {
				$segments[] = $query['view'];
			}
			unset($query['view']);

			if ($view == 'checkout' || $view == 'invoice' ||  $view == 'thanks') {

				if (isset($query['layout']) && $query['layout']) {
					$segments[] = $query['layout'];
					unset($query['layout']);
				}

				if (isset($query['invoice_key']) && $query['invoice_key']) {
					$segments[] = $query['invoice_key'];
					unset($query['invoice_key']);
				}
			}

			if ($view == 'dashboard' || $view == 'order') {
				if (isset($query['layout']) && $query['layout']) {
					$segments[] = $query['layout'];
					unset($query['layout']);
				}

				if (isset($query['subscription_key']) && $query['subscription_key']) {
					$segments[] = $query['subscription_key'];
					unset($query['subscription_key']);
				}
			}

			if ($view == 'login') {
				if (isset($query['plan_id']) && $query['plan_id']) {
					$segments[] = $query['plan_id'];
					unset($query['plan_id']);
				}
			}

			if ($view == 'download') {
				if (isset($query['layout']) && $query['layout']) {
					$segments[] = $query['layout'];
					unset($query['layout']);
				}
			}

			if ($view == 'register') {
				if (isset($query['plan_id']) && $query['plan_id']) {
					$segments[] = $query['plan_id'];
					unset($query['plan_id']);
				}

				if (isset($query['invoice_key']) && $query['invoice_key']) {
					$segments[] = $query['invoice_key'];
					unset($query['invoice_key']);
				}
			}

		}


		if (isset($query['task']) && $query['task']) {
			$segments[] = $query['task'];
			unset($query['task']);

			if (isset($query['plan_id']) && $query['plan_id']) {
				$segments[] = $query['plan_id'];
				unset($query['plan_id']);
			}
		}

		// these are the segement after tasks.

		if ($view == 'payment') {
			if (isset($query['payment_key']) && $query['payment_key']) {
				$segments[] = $query['payment_key'];
				unset($query['payment_key']);
			}
		}

		return $segments;
	}

	public static function parseRoute(&$segments)
	{
		$vars = array();

		$sysViews = self::getSysViews();

		$active = JFactory::getApplication()->getMenu()->getActive();

		$count = count($segments);

		if (isset($active->query['view']) && $active->query['view'] == 'dashboard' && isset($segments[0])) {

			// test if this is a task or not.
			if (!in_array($segments[0], $sysViews) && strpos($segments[0], '.') === false) {
				array_unshift($segments, $active->query['view']);
			}

			if (in_array($segments[0], $sysViews) && $count == 1 && $segments[0] == 'download') {
				array_unshift($segments, $active->query['view']);
			}
		}

		// recount.
		$count = count($segments);

		// menu-alias/plan.subscribe
		// menu-alias/checkout
		// menu-alias/denied

		if ($count == 1 && in_array($segments[0], $sysViews)) {
			$vars['view'] = $segments[0];
		}

		if ($count == 1 && !in_array($segments[0], $sysViews)) {
			$vars['task'] = $segments[0];
		}

		// menu-alias/checkout/8O56WJCMAUBK
		// menu-alias/invoice/8O56WJCMAUBK
		// menu-alias/thanks/8O56WJCMAUBK
		// menu-alias/payment/8O56WJCMAUBZ

		// menu-alias/dashboard/preference
		// menu-alias/dashboard/download

		// menu-alias/login/1

		if ($count == 2 && in_array($segments[0], $sysViews)) {
			$view = $segments[0];
			$vars['view'] = $segments[0];

			if ($view == 'checkout' || $view == 'invoice' || $view == 'thanks') {
				$vars['invoice_key'] = $segments[1];
			}

			if ($view == 'dashboard') {
				$vars['layout'] = $segments[1];
			}

			if ($view == 'download') {
				$vars['layout'] = $segments[1];
			}

			if ($view == 'login') {
				$vars['plan_id'] = $segments[1];
			}

			if ($view == 'payment') {
				$vars['payment_key'] = $segments[1];
			}
		}

		// menu-alias/plan.subscribe/1
		if ($count == 2 && !in_array($segments[0], $sysViews)) {
			$vars['task'] = $segments[0];
			$vars['plan_id'] = $segments[1];
		}

		// menu-alias/dashboard/subscription/8O56WJCMAUBA
		// menu-alias/order/subscription/8O56WJCMAUBA
		// menu-alias/invoice/download/8O56WJCMAUBA
		// menu-alias/register/2/8O56WJCMAUBA

		if ($count == 3 && in_array($segments[0], $sysViews)) {
			$view = $segments[0];
			$vars['view'] = $segments[0];

			if ($view == 'dashboard' ||$view == 'order') {
				$vars['layout'] = $segments[1];
				$vars['subscription_key'] = $segments[2];
			}

			if ($view == 'invoice') {
				$vars['layout'] = $segments[1];
				$vars['invoice_key'] = $segments[2];
			}

			if ($view == 'payment') {
				$vars['task'] = $segments[1];
				$vars['payment_key'] = $segments[2];
			}

			if ($view == 'register') {
				$vars['plan_id'] = $segments[1];
				$vars['invoice_key'] = $segments[2];
			}
		}

		return $vars;
	}

	private static function getSysViews()
	{
		static $views = null;

		if (is_null($views)) {

			$files = JFolder::folders(JPATH_ROOT . '/components/com_payplans/views');

			foreach ($files as $file) {
				$views[] = $file;
			}
		}

		return $views;
	}
}

/**
 * Routing class to support Joomla 3.0
 *
 * @since  4.0.2
 */
function PayplansBuildRoute(&$query)
{
	$segments = PayplansRouterBase::buildRoute($query);
	return $segments;
}

function PayplansParseRoute($segments)
{
	$vars = PayplansRouterBase::parseRoute($segments);
	return $vars;
}