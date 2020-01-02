<?php
/**
 * @package    TechJoomlaAPI_Yahoo
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
$lang->load('plg_techjoomlaAPI_plug_techjoomlaAPI_yahoo', JPATH_ADMINISTRATOR);

/**
 * TechJoomlaAPI_Yahoo Plugin
 *
 * @since  1.0
 */
class PlgTechjoomlaAPIplug_TechjoomlaAPI_Yahoo extends CMSPlugin
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
			'getRequestTokenUrl' => 'https://api.login.yahoo.com/oauth2/request_auth',
			'getAccessTokenUrl'  => 'https://api.login.yahoo.com/oauth2/get_token',
			'callbackUrl'        => Uri::root(),
			'logfilename'        => "plg_tjapi_yahooo.log.php"
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
		$plug['name'] = "Yahoo";

		// Check if keys are set
		if ($this->api_config['appKey'] == '' || $this->api_config['appSecret'] == '' || !in_array($this->_name, $config))
		{
			$plug['error_message'] = true;

			return $plug;
		}

		$plug['api_used'] = $this->_name;
		$plug['message_type'] = 'email';
		$plug['img_file_name'] = "yahoo.png";

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
		$callback = JURI::root() . substr(JRoute::_($callback, false), strlen(JURI::base(true)) + 1);

		$session = Factory::getSession();
		$session->set("techjoomla_yahoo_exception", '');
		$session->set("invitex['oauth']['yahoo']['contacts']", '');
		$session->set("invitex['oauth']['yahoo']['authorized']", false);

		// Check if keys are set
		if ($this->api_config['appKey'] == '' || $this->api_config['appSecret'] == '')
		{
			return false;
		}

		// Get request token from yahoo
		$url = $this->api_config['getRequestTokenUrl'] . "?client_id=" . $this->api_config['appKey'] .
		"&scope=sdct-r&response_type=code&redirect_uri=" . urlencode($callback);

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
	 * @return  BOOLEAN|
	 *
	 * @since   1.6
	 */
	public function get_access_token($get, $client, $callback)
	{
		$app = Factory::getApplication();
		$getAccessTokenUrl = $this->api_config['getAccessTokenUrl'];

		$postvals = array(
			"client_id" => $this->api_config['appKey'], "client_secret" => $this->api_config['appSecret'],
			"grant_type" => "authorization_code", "redirect_uri" => $callback, "code" => $get['code']
			);

		$httpRequest = HttpFactory::getHttp();
		$accessTokenData = $httpRequest->post($getAccessTokenUrl, $postvals);
		$accessTokenData = json_decode($accessTokenData->body);

		// If access token not returned then log error
		if (empty($accessTokenData->access_token))
		{
			// Show end user a human readable message
			$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_YAHOO_MAIL_API_ERROR"), 'error');
			$msg = Text::_('PLG_TECHJOOMLA_YAHOO_ACCESS_TOKEN_ERROR');
			$msg .= ' ' . json_encode($accessTokenData);
			$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_yahoo', $this->api_config['logfilename']);

			return false;
		}
		else
		{
			$this->getContacts($accessTokenData);
		}
	}

	/**
	 * Function to get contacts
	 *
	 * @return  ARRAY|BOOLEAN
	 *
	 * @since   1.6
	 */
	public function plug_techjoomlaAPI_yahooget_contacts()
	{
		$session = Factory::getSession();
		$contacts = array();

		$this->api_config['callbackUrl'] = JRoute::_(JURI::base() . 'index.php?option=com_invitex&view=invites&layout=apis');

		if ($session->get("invitex['oauth']['yahoo']['authorized']", '') === true)
		{
			$contacts = $session->get("invitex['oauth']['yahoo']['contacts']", '');
			$cnt = 0;

			foreach ($contacts->contacts->contact as $contact)
			{
				foreach ($contact->fields as $field)
				{
					if ($field->type == "email")
					{
						$emails[$cnt]['id'] = $field->value;
					}

					if ($field->type == "name")
					{
						$emails[$cnt]['first-name'] = $field->value->givenName;
						$emails[$cnt]['last-name'] = $field->value->familyName;
					}
				}

				$cnt++;
			}

			$contacts = $this->renderContacts($emails);
		}

		return $contacts;
	}

	/**
	 * Function to get contacts
	 *
	 * @param   OBJECT  $accessTokenData  access token data
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function getContacts($accessTokenData)
	{
		$getContactsUrl = "https://social.yahooapis.com/v1/user/" . $accessTokenData->xoauth_yahoo_guid . "/contacts?format=json";
		$headers = array('Authorization' => ' Bearer ' . $accessTokenData->access_token);

		$httpRequest = HttpFactory::getHttp();
		$response = $httpRequest->get($getContactsUrl, $headers);
		$response = json_decode($response->body);

		if (!empty($response->contacts))
		{
			$session = Factory::getSession();
			$session->set("invitex['oauth']['yahoo']['authorized']", true);
			$session->set("invitex['oauth']['yahoo']['contacts']", $response);

			return true;
		}
		else
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_("NO_CONTACTS"), 'error');

			if (!isset($response->contacts->contact))
			{
				$this->addLog(
					Text::_("NO_CONTACTS") . ' ' . json_encode($response), 'JLog::CRITICAL', 'techJoomla_API_yahoo', $this->api_config['logfilename']
				);
			}

			return false;
		}
	}

	/**
	 * Function to render contacts
	 *
	 * @param   ARRAY  $emails  array of emails
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function renderContacts($emails)
	{
		$count = 0;
		$r_connections = array();

		foreach ($emails as $connection)
		{
			if (isset($connection['id']))
			{
				$r_connections[$count] = new stdClass;
				$r_connections[$count]->id  = $connection['id'];
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

				$r_connections[$count]->name = '';

				if (array_key_exists('first-name', $connection) or array_key_exists('last-name', $connection))
				{
					$r_connections[$count]->name = $first_name . ' ' . $last_name;
				}
				elseif (array_key_exists('name', $connection))
				{
					if ($connection['name'])
					{
						$r_connections[$count]->name = $connection['name'];
					}
				}

				if (array_key_exists('picture-url', $connection))
				{
					$r_connections[$count]->picture_url = $connection['picture-url'];
				}
				else
				{
					$r_connections[$count]->picture_url = '';
				}
			}
			else
			{
				continue;
			}

			$count++;
		}

		return $r_connections;
	}

	/**
	 * Function to log error
	 *
	 * @param   STRING   $message          Message to be logged in the log file
	 * @param   STRING  $priority          Log priority (JLog::EMERGENCY, JLog::ALERT, JLog::CRITICAL, JLog::ERROR,
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

	/**
	 * Function to store contact
	 *
	 * @param   STRING  $client  client
	 *
	 * @param   STRING  $data    data
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	/*public function store($client,$data)
	{
		$qry = "SELECT id FROM #__techjoomlaAPI_users WHERE user_id ={$this->user->id} AND client='{$client}' AND api='{$this->_name}' ";
		$this->db->setQuery($qry);
		$id = $exists = $this->db->loadResult();
		$row = new stdClass;
		$row->id = null;
		$row->user_id = $this->user->id;
		$row->api = $this->_name;
		$row->client = $client;
		$row->token = json_encode($data);

		if ($exists)
		{
			$row->id = $id;
			$this->db->updateObject('#__techjoomlaAPI_users', $row, 'id');
		}
		else
		{
			$status = $this->db->insertObject('#__techjoomlaAPI_users', $row);
		}
	}*/

	/**
	 * Function to get token
	 *
	 * @param   STRING  $user  user
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	/*public function getToken($user = '')
	{
		$where = '';

		if ($user)
		{
			$where = ' AND user_id=' . $user;
		}

		$query = "SELECT user_id,token
		FROM #__techjoomlaAPI_users
		WHERE token<>'' AND api='{$this->_name}' " . $where;
		$this->db->setQuery($query);

		return $this->db->loadObjectlist();
	}*/

	/**
	 * Function to remove token
	 *
	 * @param   STRING  $client  client
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	/*public function remove_token($client)
	{
		if ($client != '')
		{
			$where = "AND client='{$client}' AND api='{$this->_name}'";
		}

		$qry = "UPDATE #__techjoomlaAPI_users SET token='' WHERE user_id = {$this->user->id} " . $where;
		$this->db->setQuery($qry);
		$this->db->query();
	}*/

	/**
	 * Function to get contacts status
	 *
	 * @param   STRING  $client  client
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	/*public function connectionstatus($client='')
	{
		$where = '';

		if ($client)
		{
			$where = " AND client='" . $client . "'";
		}

		$query = "SELECT token FROM #__techjoomlaAPI_users WHERE token<>'' AND user_id = {$this->user->id} AND api='{$this->_name}'" . $where;
		$this->db->setQuery($query);
		$result = $this->db->loadResult();

		if ($result)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}*/
}
