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

class plgPayplansContenthider extends PPplugins
{	
	public function onPrepareContent( &$article, &$params, $limitstart = 0 )
	{
		$exists = JString::strpos($text, '{payplans');

		if (!$exists) {
			return true;
		}
		
		// Search for this tag in the content
		$regex = "#{payplans(.*?)}(.*?){/payplans}#s";
		
		$article->text = preg_replace_callback($regex, array($this, 'process'), $article->text);
	}
	
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$regex = "#{payplans(.*?)}(.*?){/payplans}#s";

		if (is_object($row)) {
			$text = $row->text;

			$exists = JString::strpos($text, '{payplans');

			if (!$exists) {
				return true;
			}

			$text = preg_replace_callback($regex, array($this, 'process'), $text);
			$row->text = $text;
		} else {
			$text = $row;

			$exists = JString::strpos($text, '{payplans');

			if (!$exists) {
				return true;
			}

			$text = preg_replace_callback($regex, array($this, 'process'), $text);
			$row = $text;
		}

		return true;
	}

	
	/**
	 * preg_match callback to process each match
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function process($match)
	{
		$ret = '';

		if (!isset($match[2])){
			return $ret;
		}
		
		$user = PP::user();
		$userPlans = $user->getPlans(PP_SUBSCRIPTION_ACTIVE);

		// For admin, Nothing will be hidden
		if ($user->isAdmin()) {
			return $match[2];
		}

		$plans = array();

		foreach ($userPlans as $plan) {
			$plans[] = $plan->getId();
		}

		
		// For handling case of {payplans}{/payplans}
		if (empty($match[1])) {
			if($user->id) {
				if ($plans) {
					return $match[2];
				}
			}
			return $ret;
		}

		// Fetches Action: (HIDE or SHOW) from payplans tag 
		$restrictions = explode("=", $match[1]);
		$action = explode(" ", $restrictions[1]);

		if (!empty($action[1]) && $action[1] === 'HIDE') {
			$temp = $match[2];
			$match[2] = $ret;
			$ret = $temp;
		}

		if (!$user->id) {
			return $ret;
		}

		// Fetches plan ids from {payplans} tag
		$plan_ids = isset($action[0]) ? explode(',', $action[0]) : array();

		if (!count($plan_ids)) {
			if ($plans) {
				return $match[2];
			}
			
			return $ret;
		}

		// If user plan is in specified plans then show content
		if (count(array_intersect($plans, $plan_ids)) > 0) {
			return $match[2];
		}
		
		return $ret;
	}
}
