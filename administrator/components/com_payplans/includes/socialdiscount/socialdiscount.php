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

class PPSocialDiscount extends PayPlans
{
	private $medias = array('twitter');

	/**
	 * Retrieves the discount codes for a specific social media provider
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCode($media)
	{
		$media = strtolower($media);
		$code = $this->config->get('discounts_' . $media . '_code');

		return $code;
	}

	/**
	 * Determines if social discounts needs to be renders
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isEnabled()
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = false;

			if ($this->isEnabledTwitter()) {
				$enabled = true;
			}
		}

		return $enabled;
	}

	/**
	 * Determines if Twitter social discount is enabled
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isEnabledTwitter()
	{
		static $enabled = null;

		if (is_null($enabled)) {
			$enabled = false;

			if ($this->config->get('discounts_twitter') && $this->config->get('discounts_twitter_code') && $this->config->get('discounts_twitter_url')) {
				$enabled = true;
			}
		}

		return $enabled;
	}

	/**
	 * Generates the html output for social discounts
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function html(PPInvoice $invoice)
	{
		$output = array();

		foreach ($this->medias as $media) {
			$method = 'isEnabled' . ucfirst($media);
			$enabled = $this->$method();

			if ($enabled) {
				$output[] = $this->$media($invoice);
			}
		}

		$theme = PP::themes();
		$theme->set('invoice', $invoice);
		$theme->set('output', $output);
		$contents = $theme->output('site/socialdiscount/default');

		return $contents;
	}

	/**
	 * Get Twitter follow button
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function twitter(PPInvoice $invoice)
	{
		$pageUrl = $this->config->get('discounts_twitter_url', '');
		$discountCode = $this->config->get('discounts_twitter_code', '');

		// Normalize the page url
		$pageUrl = str_ireplace(array('https://twitter.com/', 'http://twitter.com/'), '', $pageUrl);

		$theme = PP::themes();
		$theme->set('pageUrl', $pageUrl);
		$theme->set('discountCode', $discountCode);

		$output = $theme->output('site/socialdiscount/twitter/default');

		return $output;
	}
}