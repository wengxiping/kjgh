<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/**
 * Google URL
 *
 * @since  1.0
 */
class Googl
{
	// Application key
	private $APIKey;

	// Api url
	private $API = "https://www.googleapis.com/urlshortener/v1/url";

	/**
	 * Constructor
	 *
	 * @param   String  $apiKey  API Keys
	 *
	 * @since 1.5
	 */
	public function __construct($apiKey = "")
	{
		if ($apiKey != "")
		{
			$this->APIKey = $apiKey;
		}
	}

	/**
	 * Get full URL
	 *
	 * @param   String  $shortURL   short URL
	 * @param   String  $analytics  Analytics
	 *
	 * @return  Object
	 */
	public function get_long($shortURL , $analytics = false)
	{
		$url = $this->API . '?shortUrl=' . $shortURL;

		if ($this->APIKey)
		{
			$url .= '&key=' . $this->APIKey;
		}

		if ($analytics)
		{
			$url .= '&projection=FULL';
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		$array = json_decode($result, true);

		return $array;
	}

	/**
	 * Get short URL
	 *
	 * @param   String  $longURL  Original URL
	 *
	 * @return  Array
	 */
	public function set_short($longURL)
	{
		$vars = "";

		if ($this->APIKey)
		{
			$vars .= "?key=$this->APIKey";
		}

		$ch = curl_init($this->API . $vars);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"longUrl": "' . $longURL . '"}');
		$result = curl_exec($ch);
		curl_close($ch);
		$array = json_decode($result, true);

		return $array;
	}
}
