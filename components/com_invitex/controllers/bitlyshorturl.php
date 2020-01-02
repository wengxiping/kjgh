<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

/**
 * Bitly
 *
 * @since  1.0
 */
class Bitly
{
	// Application key
	private $APIKey;

	// Api url
	private $API = "https://api-ssl.bit.ly/v3/shorten";

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
	 * @param   String  $longUrl       URL
	 * @param   String  $access_token  Access token
	 * @param   String  $domain        Domain
	 * @param   String  $x_login       X login
	 * @param   String  $x_apiKey      X API Key
	 *
	 * @return  Array
	 */
	public function set_short($longUrl, $access_token ='', $domain = '', $x_login = '', $x_apiKey = '')
	{
		$result = array();
		$url = $this->API . "?access_token=" . $access_token . "&longUrl=" . urlencode($longUrl);

		if ($domain != '')
		{
			$url .= "&domain=" . $domain;
		}

		if ($x_login != '' && $x_apiKey != '')
		{
			$url .= "&x_login=" . $x_login . "&x_apiKey=" . $x_apiKey;
		}

		$output = json_decode($this->bitly_get_curl($url));

		if (isset($output->{'data'}->{'hash'}))
		{
			$result['url'] = $output->{'data'}->{'url'};
			$result['hash'] = $output->{'data'}->{'hash'};
			$result['global_hash'] = $output->{'data'}->{'global_hash'};
			$result['long_url'] = $output->{'data'}->{'long_url'};
			$result['new_hash'] = $output->{'data'}->{'new_hash'};
		}

		$result['status_code'] = $output->status_code;

		return $result;
	}

	/**
	 * Get URL
	 *
	 * @param   String  $uri  Original URL
	 *
	 * @return  Array
	 */
	public function bitly_get_curl($uri)
	{
		$output = "";

		$ch = curl_init($uri);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$output = curl_exec($ch);

		return $output;
	}
}
