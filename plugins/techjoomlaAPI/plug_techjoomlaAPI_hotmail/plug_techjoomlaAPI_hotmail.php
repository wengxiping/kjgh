<?php
/**
 * @package    TechJoomlaAPI_Hotmail
 * @author     TechJoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Log\Log;

$lang = Factory::getLanguage();
$lang->load('plg_techjoomlaAPI_plug_techjoomlaAPI_hotmail', JPATH_ADMINISTRATOR);

/**
 * TechJoomlaAPI_Hotmail Plugin
 *
 * @since  1.0
 */
class PlgTechjoomlaAPIplug_TechjoomlaAPI_Hotmail extends CMSPlugin
{
	/**
	 * Api config
	 *
	 * @since  1.0
	 */
	protected $api_config = array();

	/**
	 * Constructor.
	 *
	 * @param   OBJECT  &$subject  subject.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->api_config = array(
			'appKey'             => trim($this->params->get('appKey')),
			'appSecret'          => trim($this->params->get('appSecret')),
			'getRequestTokenUrl' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
			'getAccessTokenUrl'  => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
			'callbackUrl'        => Uri::root() . 'techjoomla_hotmail_api.php',
			'logfilename'        => "plg_tjapi_hotmail.log.php"
		);
	}

	/**
	 * Get the plugin output as a separate html form
	 * NOTE: all hidden inputs returned are very important
	 *
	 * @param   ARRAY  $config  config
	 *
	 * @return  ARRAY  Data to generate html form for this plugin
	 */
	public function renderPluginHTML($config)
	{
		$plug = array();
		$plug['name'] = "Hotmail";

		// Check if keys are set
		if ($this->api_config['appKey'] == '' || $this->api_config['appSecret'] == '' || !in_array($this->_name, $config))
		{
			$plug['error_message'] = true;

			return $plug;
		}

		$plug['api_used'] = $this->_name;
		$plug['message_type'] = 'email';
		$plug['img_file_name'] = "hotmail.png";

		return $plug;
	}

	/**
	 * Function to get request tokens
	 *
	 * @param   STRING  $callback  callback
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function get_request_token($callback)
	{
		// Check if keys are set
		if ($this->api_config['appKey'] == '' || $this->api_config['appSecret'] == '')
		{
			return false;
		}

		// Get request token from hotmail
		$url = $this->api_config['getRequestTokenUrl'] . "?client_id=" . $this->api_config['appKey'] .
		"&scope=contacts.read&response_type=code&redirect_uri=" . urlencode($this->api_config['callbackUrl']);

		$app = Factory::getApplication();
		$app->redirect($url);
	}

	/**
	 * Function to get access tokens
	 *
	 * @param   STRING  $get       get
	 *
	 * @param   STRING  $client    client
	 *
	 * @param   STRING  $callback  callback
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function get_access_token($get, $client, $callback)
	{
		$app = Factory::getApplication();
		$getAccessTokenUrl = $this->api_config['getAccessTokenUrl'];

		$postvals = array(
			"client_id" => $this->api_config['appKey'], "client_secret" => $this->api_config['appSecret'],
			"grant_type" => "authorization_code", "redirect_uri" => $this->api_config['callbackUrl'], "code" => $get['code']
			);

		$httpRequest = HttpFactory::getHttp();
		$accessTokenData = $httpRequest->post($getAccessTokenUrl, $postvals);
		$accessTokenData = json_decode($accessTokenData->body);

		// If access token not returned then log error
		if (empty($accessTokenData->access_token))
		{
			// Show end user a human readable message
			$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_HOTMAIL_MAIL_API_ERROR"), 'error');
			$msg = Text::_('PLG_TECHJOOMLA_HOTMAIL_ACCESS_TOKEN_ERROR');
			$msg .= ' ' . json_encode($accessTokenData);
			$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_hotmail', $this->api_config['logfilename']);

			return false;
		}
		else
		{
			$session = Factory::getSession();
			$session->set('com_invitex_access_token_hotmail', $accessTokenData->access_token);

			return true;
		}
	}

	/**
	 * Function to get contacts
	 *
	 * @return  ARRAY|Boolean  returns contact array else false if error occurs
	 *
	 * @since   1.6
	 */
	public function plug_techjoomlaAPI_hotmailget_contacts()
	{
		$session = Factory::getSession();
		$accessToken = $session->get('com_invitex_access_token_hotmail');

		return $this->getContacts($accessToken);
	}

	/**
	 * Function to get contacts
	 *
	 * @param   STRING  $accessToken  access token
	 *
	 * @return  ARRAY|Boolean  returns contact array else false if error occurs
	 *
	 * @since   1.6
	 */
	public function getContacts($accessToken)
	{
		$app = Factory::getApplication();

		$session = Factory::getSession();
		$session->set('com_invitex_access_token_hotmail', '');

		$getContactsUrl = "https://graph.microsoft.com/v1.0/me/contacts";
		$headers = array('Authorization' => ' Bearer ' . $accessToken, 'Content-Type' => 'application/json');

		$httpRequest = HttpFactory::getHttp();
		$response = $httpRequest->get($getContactsUrl, $headers);
		$mydata = json_decode($response->body);

		if (!empty($mydata->value))
		{
			$contacts = array();
			$count = 0;

			foreach ($mydata->value as $contact)
			{
				if ($contact->emailAddresses[0]->address)
				{
					$contacts[$count]['id'] = $contact->emailAddresses[0]->address;
				}

				if ($contact->displayName)
				{
					$contacts[$count]['name'] = $contact->displayName;
				}

				$count++;
			}

			$contacts = $this->renderContacts($contacts);

			if (empty($contacts))
			{
				$app->enqueueMessage(Text::_("NO_CONTACTS"), 'error');
			}

			return $contacts;
		}
		else
		{
			$app->enqueueMessage(Text::_("NO_CONTACTS"), 'error');

			if (!isset($mydata->data))
			{
				$this->addLog(
					Text::_("NO_CONTACTS") . ' ' . json_encode($response), 'JLog::CRITICAL', 'techJoomla_API_hotmail', $this->api_config['logfilename']
				);
			}

			return false;
		}
	}

	/**
	 * Function to format contacts array
	 *
	 * @param   ARRAY  $emails  emails list
	 *
	 * @return  ARRAY
	 *
	 * @since   1.6
	 */
	public function renderContacts($emails)
	{
		$count = 0;
		$contacts = array();

		foreach ($emails as $connection)
		{
			if ($connection['id'])
			{
				$contacts[$count] = new stdClass;
				$contacts[$count]->id = $connection['id'];
				$first_name = '';
				$last_name = '';

				if (array_key_exists('first-name', $connection))
				{
					$first_name = $connection['first-name'];
				}

				if (array_key_exists('last-name', $connection))
				{
					$last_name = $connection['last-name'];
				}

				if (array_key_exists('first-name', $connection) || array_key_exists('last-name', $connection))
				{
					$contacts[$count]->name = $first_name . ' ' . $last_name;
				}

				if (trim($first_name) == '' && trim($last_name) == '')
				{
					if (array_key_exists('name', $connection))
					{
						$contacts[$count]->name = $connection['name'];
					}
				}

				if (array_key_exists('picture-url', $connection))
				{
					$contacts[$count]->picture_url = $connection['picture-url'];
				}
				else
				{
					$contacts[$count]->picture_url = '';
				}

				$count++;
			}
		}

		return $contacts;
	}

	/**
	 * Function to log error
	 *
	 * @param   STRING   $message          Message to be logged in the log file
	 * @param   STRING   $priority         Log priority (JLog::EMERGENCY, JLog::ALERT, JLog::CRITICAL, JLog::ERROR,
	 *                                     JLog::WARNING, JLog::NOTICE, JLog::INFO, JLog::DEBUG)
	 * @param   STRING   $category         Log category
	 * @param   STRING   $logFileName      Log file name
	 * @param   STRING   $textEntryFormat  Log message entry format
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function addLog($message, $priority, $category = 'tj', $logFileName = '', $textEntryFormat = '{DATETIME} {PRIORITY} {MESSAGE}')
	{
		if (!empty($logFileName))
		{
			// Add logger to add the logs in
			Log::addLogger(
				array(
					'text_file' => $logFileName,
					'text_entry_format' => $textEntryFormat
				),
				Log::ALL,
				array($category)
			);
		}

		Log::add($message, $priority, $category);
	}
}
