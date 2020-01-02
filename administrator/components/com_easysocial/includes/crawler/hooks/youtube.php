<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/abstract.php');

class SocialCrawlerYoutube extends SocialCrawlerAbstract
{
	public $oembed = null;

	public function process(&$result)
	{
		// Check if the url should be processed here.
		if (stristr($this->url, 'youtube.com') === false || strstr($this->url, 'results?search_query')) {
			return;
		}

		parse_str(parse_url($this->url, PHP_URL_QUERY), $data);

		if (!$data) {
			return;
		}

		// Bind result
		$this->oembed = $result->oembed;

		// Process youtube API
		$state = $this->youtubeAPI();

		// If we can't get any oembed data from youtube, we will then simulate this.
		if (!$state) {
			$this->simulateOembed();

			// Try to get the duration from the contents
			$this->getDuration();

			$this->getThumbnail();
		}

		$result->oembed = $this->oembed;
	}

	/**
	 * Process youtube via API v3
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function youtubeAPI()
	{
		$config = ES::config();
		$key = trim($config->get('youtube.api.key'));
		$enabled = $config->get('youtube.api.enabled');

		if (!$enabled || !$key) {
			return false;
		}

		// Get the video id.
		parse_str(parse_url($this->url, PHP_URL_QUERY), $videoId);

		$videoId = $videoId['v'];

		$parts = "&fields=items(id,snippet(title,description,thumbnails(standard)),contentDetails(duration))&part=snippet,contentDetails";

		// Connect to youtube api.
		$url = "https://www.googleapis.com/youtube/v3/videos?id=". $videoId ."&key=" . $key . $parts;

		$connector = ES::connector();
		$connector->addUrl($url);

		// Required if admin decided to restrict the api with http referrer. #497
		$connector->addReferrer(JUri::root());

		$connector->connect();

		$contents = $connector->getResult($url);

		$obj = json_decode($contents);

		// If connection failed, return to default oembed value.
		if (!$obj) {
			return false;
		}

		// There are some errors when trying to validate the key
		if (isset($obj->error)) {

			// // Debug
			// dump($obj->error);

			return false;
		}

		$oembed = new stdClass();

		// Assign oembed data
		foreach ($obj->items as $item) {
			$oembed->html = '<iframe width="480" height="270" src="https://www.youtube.com/embed/'. $item->id .'?feature=oembed" frameborder="0" allowfullscreen></iframe>';
			$oembed->width = 480;
			$oembed->height = 270;

			$snippet = isset($item->snippet) ? $item->snippet : null;

			// bind the video snippet
			if ($snippet) {
				$oembed->title = $snippet->title;
				$oembed->description = $snippet->description;
				$oembed->thumbnail = 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
				$oembed->thumbnail_url = 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';

				$thumbnails = isset($snippet->thumbnails) ? $snippet->thumbnails : null;

				// Use the provided thumbnails if exists.
				if ($thumbnails) {
					$oembed->thumbnail = $thumbnails->standard->url;
					$oembed->thumbnail_url = $thumbnails->standard->url;
				}
			}

			// Get duration
			$oembed->duration = $item->contentDetails->duration;
		}

		$this->oembed = $oembed;

		// Format the duration
		$this->getDuration();

		return true;
	}

	/**
	 * Simulate oembed data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function simulateOembed()
	{
		// get the video id.
		parse_str(parse_url($this->url, PHP_URL_QUERY), $video);
		$id = $video['v'];

		$oembed = new stdClass();

		// Hard code the neccessary value.
		$oembed->height = 270;
		$oembed->width = 480;
		$oembed->html = '<iframe width="480" height="270" src="https://www.youtube.com/embed/'. $id .'?feature=oembed" frameborder="0" allowfullscreen></iframe>';
		$oembed->thumbnail = 'https://img.youtube.com/vi/'. $id .'/sddefault.jpg';
		$oembed->thumbnail_url = 'https://img.youtube.com/vi/'. $id .'/sddefault.jpg';

		$this->oembed = $oembed;
	}

	/**
	 * Get video thumbnails
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getThumbnail()
	{
		// We want to get the HD version of the thumbnail
		$thumbnail = str_ireplace('sddefault.jpg', 'hqdefault.jpg', $this->oembed->thumbnail);

		// Try to get the sd details
		$connector = ES::connector();
		$connector->addUrl($thumbnail);
		$connector->useHeadersOnly();
		$connector->connect();

		$headers = $connector->getResult($thumbnail, true);

		// If the image exists, we just use the sd version
		$notFound = stristr($headers, 'HTTP/1.1 404 Not Found');

		if ($notFound === false) {
			$this->oembed->thumbnail = $thumbnail;
			$this->oembed->thumbnail_url = $thumbnail;
		}
	}

	/**
	 * Convert video duration from  ISO 8601 format to seconds.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getDuration()
	{
		// Get the duration
		if (isset($this->oembed->duration) && $this->oembed->duration) {
			$duration = $this->oembed->duration;
		} else {
			$node = $this->parser->find('[itemprop=duration]');

			$node = $node[0];

			$duration = $node->attr['content'];
		}

		// Match the duration
		$pattern = '/PT(\d+)H|(\d+)M(\d+)S/i';

		// $matches = preg_match($pattern, $duration);
		preg_match_all($pattern, $duration, $matches);

		$seconds = 0;

		// Get the hour
		if (isset($matches[1]) && $matches[1]) {
			if ($matches[1][0] === "") {
				$matches[1][0] = 0;
			}

			$seconds = $matches[1][0] * 60 * 60;
		}

		// Minutes
		if (isset($matches[2]) && $matches[2]) {
			if ($matches[2][0] === "") {
				$matches[2][0] = 0;
			}

			$seconds = $seconds + ($matches[2][0] * 60);
		}

		// Seconds
		if (isset($matches[3]) && $matches[3]) {
			if ($matches[3][0] === "") {
				$matches[3][0] = 0;
			}

			$seconds = $seconds + $matches[3][0];
		}

		$this->oembed->duration = (int) $seconds;
	}
}
