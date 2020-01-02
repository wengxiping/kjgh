<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerTwitch extends SocialCrawlerAbstract
{
	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'twitch.tv') === false) {
			return;
		}

		// Generate the oembed url
		$oembed = $this->crawl($this->url);

		// Live videos does not have any oembed data
		if (!$oembed) {

			// Reads this URL html content
			$htmlContent = @file_get_contents($this->url);

			// Retrieve the opengraph data
			$ogContent = $this->getOpengraphData($htmlContent);

			// Manually generate embed codes.
			$embedCodes = $this->generateVideoEmbed($this->url);

			if (isset($result->opengraph->image) && stristr($result->opengraph->image, 'http://') === false && stristr($result->opengraph->image, 'https://') === false) {
				$result->opengraph->image = 'https:' . $result->opengraph->image;
			}

			if ($embedCodes !== false) {
				
				$result->oembed->type = 'embed';
				$result->oembed->html = $embedCodes;

				if (isset($ogContent->title)) {
					$result->title = $ogContent->title;
				}

				if (isset($ogContent->image)) {
					$result->oembed->thumbnail = $ogContent->image;

					if (stristr($result->oembed->thumbnail, 'http://') === false && stristr($result->oembed->thumbnail, 'https://') === false) {
						$result->oembed->thumbnail = 'https:' . $result->oembed->thumbnail;
					}
				}

				if (isset($ogContent->video_duration)) {
					$result->oembed->duration = $ogContent->video_duration;
				}
			}

			return $result;
		}

		// Try to get the duration from the contents
		$oembed->duration = $oembed->video_length;
		$oembed->thumbnail = $oembed->thumbnail_url;

		$result->oembed = $oembed;

		// Do not use the opengraph data if there is an oembed data
		if ($result->oembed) {
			unset($result->opengraph);
		}
	}

	public function crawl($url)
	{
		// Need to ensure the URL is always twitch.tv
		$url = str_ireplace('go.twitch.tv', 'twitch.tv', $url);
		$endpoint = 'https://api.twitch.tv/v5/oembed?url=' . urlencode($url);

		$connector = ES::connector();
		$connector->addUrl($endpoint);
		$connector->connect();

		$contents = $connector->getResult($endpoint);

		$oembed = json_decode($contents);

		return $oembed;
	}

	/**
	 * Since some of the Twitch video doesn't have the consistence data, we need to extract these data ourselves
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	private function generateVideoEmbed($url)
	{
		$url = explode('/', $url);
		$channel = array_pop($url);

		$output = '<iframe src="https://player.twitch.tv/?channel=' . $channel . '&autoplay=false" width="100%" height="480" frameborder="0" allowfullscreen></iframe>';

		return $output;
	}	
}
