<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerLiveleak extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'liveleak.com') === false) {
			return;
		}

		$oembed = $this->getOembed();

		// If we can't get any oembed data, We try to find embed content instead
		if (!$oembed) {
			$oembed = $this->getEmbedContent();
		}

		if (!$oembed) {
			return;
		}

		// Fix http url in https issue
		$oembed = $this->fixOembedUrl($oembed);

		$result->oembed = $oembed;
	}

	/**
	 * Get the embed from the content
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getEmbedContent()
	{
		$oembed = new stdClass();
		$oembed->html = '';

		preg_match('/view\?t=(.*)/i', $this->url, $matches);

		// If the matches is empty, try a legacy liveleaks url
		if (empty($matches)) {
			preg_match('/view\?i=(.*)/i', $this->url, $matches);
		}

		if (!empty($matches)) {
			$code = $matches[1];

			if ($code) {
				$oembed->html = '<iframe width="640" height="360" src="https://www.liveleak.com/e/' . $code . '" frameborder="0" allowfullscreen></iframe>';
			}
		}

		// Try get the thumbnail
		$items = $this->parser->find('meta[property=og:image]');

		foreach ($items as $meta) {

			if (!$meta->content) {
				continue;
			}

			$url = $meta->content;

			if (stristr($url, 'http://') === false && stristr($url, 'https://') === false) {
				$url = 'http://' . $url;
			}

			$oembed->thumbnail = $url;
		}

		return $oembed;
	}
}
