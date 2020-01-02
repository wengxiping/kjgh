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
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class PayPlansRecaptchaResponse
{
	public $success;
	public $errorCodes;
}

class PPCaptcha extends PayPlans
{
	private static $_signupUrl = "https://www.google.com/recaptcha/admin";
	private static $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
	private $_secret;
	private static $_version = "php_1.0";
	private $options = array();

	public function __construct($options = array())
	{
		parent::__construct();

		$this->options['key'] = $this->config->get('recaptcha_sitekey');
		$this->options['secret'] = $this->config->get('recaptcha_secretkey');
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param array $data array of string elements to be encoded.
	 *
	 * @return string - encoded request.
	 */
	private function _encodeQS($data)
	{
		$req = "";
		foreach ($data as $key => $value) {
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req=substr($req, 0, strlen($req)-1);
		return $req;
	}

	/**
	 * Connects to recaptcha server for verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	private function connect($path, $data)
	{
		$req = $this->_encodeQS($data);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $path . $req);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CAINFO, PP_CACERT);

		$response = curl_exec($ch);

		curl_close($ch);

		return $response;
	}

	/**
	 * Retrieve recaptcha language from json file
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getRecaptchaLanguages()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_payplans/defaults/recaptcha.json';
		
		$contents = JFile::read($file);
		$languages = json_decode($contents);

		return $languages;
	}

	/**
	 * Displays the recaptcha html code
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function html()
	{
		if (!$this->config->get('show_captcha')) {
			return;
		}

		$uid = uniqid();

		$key = $this->options['key'];

		$language = JFactory::getLanguage()->getTag();

		if ($this->config->get('default_recaptcha_language') == 'none'){
			$language = $this->config->get('recaptcha_language');
		}
		

		$invisible = $this->config->get('recaptcha_invisible');
		$color = $this->config->get('recaptcha_theme');

			
		$theme = PP::themes();
		$theme->set('uid', $uid);
		$theme->set('key', $key);
		$theme->set('color', $color);
		$theme->set('language', $language);
		$theme->set('invisible', $invisible);

		$output = $theme->output('site/recaptcha/default');

		return $output;
	}

	/**
	 * Verifies user response
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function verify($remoteIp, $response)
	{
		$recaptchaResponse = new PayPlansRecaptchaResponse();
		$recaptchaResponse->success = true;
		$recaptchaResponse->message = '';

		// Discard empty solution submissions
		if ($response == null || strlen($response) == 0) {
			$recaptchaResponse->success = false;
			$recaptchaResponse->message = JText::_('Recaptcha response not provided');
			return $recaptchaResponse;
		}

		$getResponse = $this->connect(self::$_siteVerifyUrl, array (
				'secret' => $this->options['secret'],
				'remoteip' => $remoteIp,
				'v' => self::$_version,
				'response' => $response
			)
		);

		$answers = json_decode($getResponse, true);
		
		if (trim($answers ['success']) == false) {
			$recaptchaResponse->success = false;
			$recaptchaResponse->message = JText::_('COM_PP_INVALID_CAPTCHA_RESPONSE');

			return $recaptchaResponse;
		} 

		return true;
	}
}