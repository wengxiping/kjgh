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

class PPHelperHttpquery extends PPHelperStandardApp
{
	/**
	 * Retrieves the url to be connected to
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getUrl($state)
	{
		$query = $this->params->get('urlOn' . ucfirst($state));

		return $query;
	}

	/**
	 * Retrieves the query to be executed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getQuery($state)
	{
		$query = $this->params->get('queryOn' . ucfirst($state));

		return $query;
	}

	/**
	 * Merges the url and query
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getFullUrl(PPSubscription $subscription, $url, $query)
	{
		$rewriter = PP::rewriter();
		$query = $rewriter->rewrite($query, $subscription);

		$urlsplit = explode('?', $url);

		$p = explode("\n", $query);

		if (!empty($urlsplit[1])) {
			$p2 = explode('&', $urlsplit[1]);

			if (!empty($p2)) {
				$p = array_merge($p2, $p);
			}
		}

		$fullp = array();

		foreach ($p as $entry) {
			$e = explode('=', $entry);

			if (!empty($e[0]) && !empty($e[1])) {
				$fullp[] = urlencode(trim($e[0]) ) . '=' . urlencode(trim($e[1]));
			}
		}

		return $urlsplit[0] . '?' . implode('&', $fullp);
	}

	/**
	 * Executes query
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function executeQuery(PPSubscription $subscription, $url, $query)
	{
		if (!$query || !$url) {
			return false;
		}

		$url = $this->getFullUrl($subscription, $url, $query);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$response = curl_exec($ch);
		$content = array('response' => $response, 'body' => '');
		$message = JText::_('COM_PAYPLANS_HTTP_QUERY_EXECUTE_SUCCESSFULLY');

		if ($response === false) {
			$content = array('response' => $response, 'body' => JText::sprintf('Unable to connect to %1$s. Error: %2$s (%3$s)', $url, curl_error($ch), curl_errno($ch)));
			$message = JText::_('COM_PAYPLANS_HTTP_QUERY_EXECUTE_UNSUCCESSFULLY');
		}

		PPLog::log(PPLogger::LEVEL_INFO, $message, $this->app, $content, 'PayplansAppHttpqueryFormatter');
		
		curl_close($ch);
	}
}
