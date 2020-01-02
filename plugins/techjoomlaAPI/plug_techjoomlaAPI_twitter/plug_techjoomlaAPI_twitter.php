<?php
/**
 * @package    TechJoomlaAPI_Twitter
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

jimport('joomla.plugin.plugin');

// Import twitter library
JLoader::import('plugins.techjoomlaAPI.plug_techjoomlaAPI_twitter.plug_techjoomlaAPI_twitter.lib.twitteroauth.autoload', JPATH_SITE);
use Abraham\TwitterOAuth\TwitterOAuth;

$lang = Factory::getLanguage();
$lang->load('plg_techjoomlaAPI_plug_techjoomlaAPI_twitter', JPATH_ADMINISTRATOR);

/**
 * TechJoomlaAPI_Twitter Plugin
 *
 * @since  1.0
 */
class PlgTechjoomlaAPIplug_TechjoomlaAPI_Twitter extends CMSPlugin
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
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->api_config = array(
			'appKey'             => trim($this->params->get('appKey')),
			'appSecret'          => trim($this->params->get('appSecret')),
			'getRequestTokenUrl' => 'https://api.login.yahoo.com/oauth2/request_auth',
			'getAccessTokenUrl'  => 'https://api.login.yahoo.com/oauth2/get_token',
			'callbackUrl'        => Uri::root(),
			'logfilename'        => "plg_tjapi_twitter.log.php"
		);

		$this->user = Factory::getUser();
	}

	/**
	 * Get the plugin output as a separate html form
	 * NOTE: all hidden inputs returned are very important
	 *
	 * @param   ARRAY  $config  config
	 *
	 * @return  ARRAY  Data to generate html form for this plugin
	 */
	public function renderPluginHTML($config = array())
	{
		$plug = array();

		$plug['name'] = "Twitter";

		// Check if keys are set
		if ($this->api_config['appKey'] == '' || $this->api_config['appSecret'] == '' || !in_array($this->_name, $config))
		{
			$plug['error_message'] = true;

			return $plug;
		}

		$plug['api_used']      = $this->_name;
		$plug['message_type']  = 'pm';
		$plug['img_file_name'] = "twitter.png";

		return $plug;
	}

	/**
	 * Function to get request token
	 *
	 * @param   STRING  $callback  Callback URL
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function get_request_token($callback)
	{
		$callback = JURI::root() . substr(JRoute::_($callback, false), strlen(JURI::base(true)) + 1);

		$app = Factory::getApplication();

		// Get reuest token from twitter for the developer app
		$connection = new TwitterOAuth($this->api_config['appKey'], $this->api_config['appSecret']);
		$requestToken = $connection->oauth('oauth/request_token', ['oauth_callback' => $callback]);

		$session = Factory::getSession();
		$session->set("['oauth']['twitter']['request']", '');
		$session->set("['oauth']['twitter']['access']", '');
		$session->set("['oauth']['twitter']['contacts']", '');
		$session->set("['oauth']['twitter']['contacts']['session']", '');

		if (!empty($requestToken['oauth_token']) || !empty($requestToken['oauth_token_secret']))
		{
			$session->set("['oauth']['twitter']['request']", $requestToken);

			// Redirect to twitter for authentication
			$url = $connection->url('oauth/authorize', ['oauth_token' => $requestToken['oauth_token']]);
			$app->redirect($url);
		}
		else
		{
			// Show end user a human readable message
			$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_TWITTER_API_ERROR"), 'error');

			// Log error
			$msg = Text::_('PLG_TECHJOOMLA_TWITTER_REQUEST_ERROR');
			$msg .= ' ' . json_encode($requestToken);
			$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_twitter', $this->api_config['logfilename']);

			return false;
		}
	}

	/**
	 * Function to get access token
	 *
	 * @param   STRING  $get       Data
	 * @param   STRING  $client    client component name
	 * @param   STRING  $callback  Callback URL
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function get_access_token($get, $client = '', $callback = '')
	{
		$app = Factory::getApplication();
		$session = Factory::getSession();
		$requestToken = $session->get("['oauth']['twitter']['request']");

		// If user cancels the request
		if (isset($get['denied']))
		{
			return false;
		}

		if (isset($get['oauth_verifier']))
		{
			// Get access token
			$connection = new TwitterOAuth(
			$this->api_config['appKey'], $this->api_config['appSecret'], $requestToken['oauth_token'], $requestToken['oauth_token_secret']
			);
			$accessToken = $connection->oauth("oauth/access_token", ["oauth_verifier" => $get['oauth_verifier']]);

			if (!empty($accessToken['oauth_token']) && !empty($accessToken['oauth_token_secret']))
			{
				$session->set("['oauth']['twitter']['access']", $accessToken);
				$session->set("['oauth']['twitter']['authorized']", true);

				// Store access token to be used at the time of sending messages
				$this->store($client, $accessToken);

				return true;
			}
			else
			{
				// Show end user a human readable message
				$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_TWITTER_API_ERROR"), 'error');

				// Log error
				$msg = Text::_('PLG_TECHJOOMLA_TWITTER_ACCESS_ERROR');
				$msg .= ' ' . json_encode($accessToken);
				$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_twitter', $this->api_config['logfilename']);

				return false;
			}
		}
		else
		{
			// Show end user a human readable message
			$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_TWITTER_API_ERROR"), 'error');

			// Log error
			$msg = Text::_('PLG_TECHJOOMLA_TWITTER_OAUTH_VERIFIER_ERROR');
			$msg .= ' ' . json_encode($get);
			$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_twitter', $this->api_config['logfilename']);

			return false;
		}
	}

	/**
	 * Function to store user token
	 *
	 * @param   STRING  $client  Client component name
	 * @param   ARRAY   $data    Token Data
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function store($client, $data)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__techjoomlaAPI_users'));
		$query->where($db->quoteName('api') . ' = ' . $db->quote($this->_name));
		$query->where($db->quoteName('user_id') . ' = ' . $this->user->id);

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);
		$id = $db->loadResult();

		$row          = new stdClass;
		$row->id      = null;
		$row->user_id = $this->user->id;
		$row->api     = $this->_name;
		$row->client  = $client;
		$row->token   = json_encode($data);

		if (!empty($id))
		{
			$row->id = $id;
			$return  = $db->updateObject('#__techjoomlaAPI_users', $row, 'id');
		}
		else
		{
			$return = $db->insertObject('#__techjoomlaAPI_users', $row);
		}

		return $return;
	}

	/**
	 * Function to delete user token
	 *
	 * @param   INT     $user    user id
	 * @param   STRING  $client  client component name
	 *
	 * @return  OBJECT|ARRAY
	 *
	 * @since   1.6
	 */
	public function getToken($user = '', $client = '')
	{
		// Remove deleted users entry
		$this->removeDeletedUsers();

		$db = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array($db->quoteName('user_id'), $db->quoteName('token')));
		$query->from($db->quoteName('#__techjoomlaAPI_users'));
		$query->where($db->quoteName('token') . ' <> ' . '');
		$query->where($db->quoteName('api') . ' = ' . $db->quote($this->_name));

		if (!empty($user))
		{
			$query->where($db->quoteName('user_id') . ' = ' . $user);
		}

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Function to remove tokes of deleted users
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function removeDeletedUsers()
	{
		$db = Factory::getDbo();

		$subQuery = $db->getQuery(true);
		$subQuery->select($db->quoteName('id'));
		$subQuery->from($db->quoteName('#__users'));

		$query = $db->getQuery(true);
		$query->select($db->quoteName('user_id'));
		$query->from($db->quoteName('#__techjoomlaAPI_users'));
		$query->where($db->quoteName('user_id') . ' NOT IN (' . $subQuery . ')');

		$db->setQuery($query);
		$deletedUsers = $db->loadObjectList();

		foreach ($deletedUsers as $deletedUser)
		{
			$query = $db->getQuery(true);

			$query->where($db->quoteName('user_id') . ' = ' . $deletedUser->user_id);
			$query->delete($db->quoteName('#__techjoomlaAPI_users'));

			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Function to delete user token
	 *
	 * @param   STRING  $client  client component name
	 *
	 * @return  BOOLEAN
	 *
	 * @since   1.6
	 */
	public function remove_token($client)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if ($client != '')
		{
			$conditions = array(
				$db->quoteName('client') . ' = ' . $db->quote($client),
				$db->quoteName('api') . ' = ' . $db->quote($this->_name)
			);

			$query->where($conditions);
		}

		$query->where($db->quoteName('user_id') . ' = ' . $this->user->id);
		$query->delete($db->quoteName('#__techjoomlaAPI_users'));

		$db->setQuery($query);
		$result = $db->execute();

		return $result;
	}

	/**
	 * Function to get users followers
	 *
	 * @param   INT  $offset  Offset
	 * @param   INT  $limit   limit
	 *
	 * @return  ARRAY|BOOLEAN  contacts data, false if error occurs
	 *
	 * @since   1.6
	 */
	public function plug_techjoomlaAPI_twitterget_contacts($offset = 0, $limit = 99)
	{
		$limit = 99;
		$app = Factory::getApplication();
		$session = Factory::getSession();
		$accessToken = $session->get("['oauth']['twitter']['access']", '');

		if (isset($accessToken['oauth_token']) && isset($accessToken['oauth_token_secret']))
		{
			$connection = new TwitterOAuth(
			$this->api_config['appKey'], $this->api_config['appSecret'], $accessToken['oauth_token'], $accessToken['oauth_token_secret']
			);
		}

		// For first load set the cursor on the start point
		if ($offset == 0)
		{
			$session->set("['oauth']['twitter']['contacts']['next_cursor']", '');
		}

		// Get the current cursor
		$nextCursor = $session->get("['oauth']['twitter']['contacts']['next_cursor']", '');

		$response = new stdclass;

		// Get the list of followers
		if ($nextCursor !== 0)
		{
			if (!empty($nextCursor))
			{
				$response = $connection->get("followers/ids", ['count' => $limit, 'cursor' => $nextCursor]);
			}
			else
			{
				$response = $connection->get("followers/ids", ['count' => $limit]);
			}

			// Update the cursor
			$session->set("['oauth']['twitter']['contacts']['next_cursor']", $response->next_cursor);
		}

		if (isset($response->ids) && count($response->ids) != 0)
		{
			$session->set("['oauth']['twitter']['contacts']", $response->ids);
		}
		else
		{
			// Show end user a human readable message
			$app->enqueueMessage(Text::_("PLG_TECHJOOMLA_TWITTER_API_NO_CONTACTS"), 'info');

			if (!isset($response->ids))
			{
				// Log error
				$msg = Text::_('PLG_TECHJOOMLA_TWITTER_GET_CONTACTS_ERROR');
				$msg .= ' ' . json_encode($accessToken);
				$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_twitter', $this->api_config['logfilename']);

				return false;
			}
		}

		$contacts = $tot_contacts = $session->get("['oauth']['twitter']['contacts']", '');

		if (is_array($contacts))
		{
			array_splice($contacts, $limit);
		}

		if ($contacts)
		{
			$profiles = $connection->get("users/lookup", ['user_id' => implode(',', $contacts)]);

			$followers = array();

			if (count($profiles) != 0)
			{
				$i = 0;

				foreach ($profiles as $userprofile)
				{
					$followers[$i]['id']          = $userprofile->screen_name;
					$followers[$i]['name']        = $userprofile->name;
					$followers[$i]['picture-url'] = $userprofile->profile_image_url_https;
					$i++;
				}
			}

			if (is_array($tot_contacts))
			{
				$remain = array_slice($tot_contacts, 99);
				$session->set("['oauth']['twitter']['contacts']", $remain);
			}

			$contacts = $this->renderContacts($followers);

			return $contacts;
		}
		else
		{
			return array();
		}
	}

	/**
	 * Function to send twitter messages
	 *
	 * @param   ARRAY  $emails  Emails Array
	 *
	 * @return  ARRAY
	 *
	 * @since   1.6
	 */
	public function renderContacts($emails)
	{
		$count = 0;
		$r_connections = array();

		foreach ($emails as $connection)
		{
			$r_connections[$count] = new stdClass;
			$r_connections[$count]->id = $connection['id'];
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
				$r_connections[$count]->name = $first_name . ' ' . $last_name;
			}
			elseif (array_key_exists('name', $connection))
			{
				$r_connections[$count]->name = $connection['name'];
			}

			if ($connection['picture-url'])
			{
				$r_connections[$count]->picture_url = $connection['picture-url'];
			}
			else
			{
				$r_connections[$count]->picture_url = '';
			}

			$count++;
		}

		return $r_connections;
	}

	/**
	 * Function to send twitter messages
	 *
	 * @param   ARRAY  $mail  Data for sending message
	 * @param   ARRAY  $post  Data for sending message
	 *
	 * @return  ARRAY
	 *
	 * @since   1.6
	 */
	public function plug_techjoomlaAPI_twittersend_message($mail, $post)
	{
		JLoader::import('components.com_invitex.helper', JPATH_SITE);
		$cominvitexHelper = new cominvitexHelper;
		$this->invitex_params = $cominvitexHelper->getconfigData();

		if ($post['invite_type'] > 0)
		{
			$types_res = $cominvitexHelper->types_data($post['invite_type']);
			$template  = stripslashes($types_res->template_twitter);
		}
		else
		{
			$template = stripslashes($this->invitex_params->get('twitter_message_body'));
		}

		$token = $post['token'];
		$token = json_decode($token);
		$token = (array) $token;

		$return = array();

		$mail['msg_body'] = $template;
		$message = $cominvitexHelper->tagreplace($mail, "bitly");

		$connection = new TwitterOAuth($this->api_config['appKey'], $this->api_config['appSecret'], $token['oauth_token'], $token['oauth_token_secret']);

		$profile = $connection->get("users/lookup", ['screen_name' => $post['invitee_email']]);

		$data = [
			'event' => [
				'type' => 'message_create',
				'message_create' => [
					'target' => [
						'recipient_id' => $profile[0]->id
					],
					'message_data' => [
						'text' => $message
					]
				]
			]
		];

		// Send message
		$response = $connection->post("direct_messages/events/new", $data, true);

		if ($response->event->id)
		{
			$return[0] = 1;
			$return[1] = $response;
		}
		else
		{
			// Error in sending direct message
			$return[0] = -1;
			$return[1] = $response;

			// Log error
			$msg = Text::_('PLG_TECHJOOMLA_TWITTER_SEND_MESSAGE_ERROR');
			$msg .= ' ' . json_encode($response);
			$this->addLog($msg, 'JLog::CRITICAL', 'techJoomla_API_twitter', $this->api_config['logfilename']);
		}

		return $return;
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

	/*function plug_techjoomlaAPI_twitterget_profile($integr_with,$client,$callback)
	{
		$session = JFactory::getSession();
		$mapData[0]		=& $this->params->get('mapping_field_0');	//joomla
		$mapData[1]		=& $this->params->get('mapping_field_1'); //jomsocial
		$mapData[2]		=& $this->params->get('mapping_field_2'); //cb

		$token = $session->get("['oauth']['twitter']['access']",'');
		$tmhOAuth = new tmhOAuth(array(
				'consumer_key'    =>trim($this->api_config['appKey']),
  			'consumer_secret' => trim($this->api_config['appSecret']),
				'user_token'      => trim($token['oauth_token']),
				'user_secret'     => trim($token['oauth_token_secret']),
				'curl_ssl_verifypeer'   => false));

			$params=array();
			$connection=array();

		$oauth_key = $this->getToken($this->user->id,'profileimport');
		if(!$oauth_key)
		return false;
		else
		$token =json_decode($oauth_key[0]->token,true);
		$params = array('user_id'=>$token['user_id'],'screen_name'=>$token['screen_name']);
		$data = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/users/show'),$params);
		$profileData=json_decode($tmhOAuth->response['response'],true);

		if($profileData)
		{
			$profileDetails['profileData']=$profileData;
			$profileDetails['mapData']=$mapData;
			return $profileDetails;
		}
	}

	function plug_techjoomlaAPI_twittersetstatus($userid='',$originalContent,$comment,$attachment='')
	{
		$oauth_key = $this->getToken($userid,'broadcast');

		if(!$oauth_key)
		return false;
		else
		$token =json_decode($oauth_key[0]->token,true);

		$tmhOAuth = new tmhOAuth(array(
		 'consumer_key'    =>trim($this->api_config['appKey']),
  			'consumer_secret' => trim($this->api_config['appSecret']),
				'user_token'      => trim($token['oauth_token']),
				'user_secret'     => trim($token['oauth_token_secret']),
				'curl_ssl_verifypeer'   => false));
			$method = "https://userstream.twitter.com/2/user.json";
			$params = array(
				// parameters go here
			);

			$code = $tmhOAuth->request('POST', $tmhOAuth->url('1.1/statuses/update'), array('status' => $originalContent));
			if($code=200)
			{
					$response=$this->raiseLog(JText::_('LOG_SET_STATUS_SUCCESS')."=>".$originalContent,JText::_('LOG_SET_STATUS'),$userid,1,200);
					return true;
			}
			else
			{
				$response=$this->raiseLog(JText::_('LOG_SET_STATUS_FAIL')."=>".$originalContent,JText::_('LOG_SET_STATUS'),$userid,1,$code);
				return false;

			}
	}

	function renderstatus($response)
	{
		if($response)
		{
			if(count($response)>=1)
			{
			$j=0;
			if(empty($response))
			return array();
			foreach($response as $data)
			{
				if($j==10)
				break;
				if(!empty($data['text']))
				{
					if( !($data['source']=='web') and  !empty($data['entities']['urls']))		//for converting the urls t.co into goo.gl
					{
						foreach($data['entities']['urls'] as $url)
						{
							$data['text'] = str_replace($url['url'],$url['expanded_url'],$data['text']);
						}
					}
					$status[$j]['comment'] =  $data['text'];
					$status[$j]['timestamp'] = strtotime($data['created_at']);
					$config =JFactory::getConfig();
					$offset = $config->get('config.offset');
					$get_date=JFactory::getDate($status[$j]['timestamp'],$offset);
					$status[$j]['timestamp'] = strtotime($get_date->format("Y-m-d"));
					$j++;
				}

			}
			return $status;

			}
		}
		else
		return array();

	}

	function plug_techjoomlaAPI_twittergetstatus()
	{
	 $oauth_keys =array();
	 $oauth_keys = $this->getToken('','broadcast');
	 if(!$oauth_keys)
		return false;
		$i=0;
		$returndata=array(array());
		if(empty($oauth_keys))
		return;
	 	foreach($oauth_keys as $oauth_key)
	 	{
	 		if(empty($oauth_key->token))
			continue;
				$token =	json_decode($oauth_key->token,true);
				$tmhOAuth = new tmhOAuth(array(
				'consumer_key'    =>trim($this->api_config['appKey']),
				'consumer_secret' => trim($this->api_config['appSecret']),
				'user_token'      => trim($token['oauth_token']),
				'user_secret'     => trim($token['oauth_token_secret']),
				'curl_ssl_verifypeer'   => false));

				if($this->params->get('broadcast_limit'))
				$twitter_profile_limit=$this->params->get('broadcast_limit');
				else
				$twitter_profile_limit=2;

				$params = array('count'=>$twitter_profile_limit,'user_id'=>$token['user_id'],'include_entities'=>1,'screen_name'=>$token['screen_name']);
				try{
				$tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'),$params);
				}
				catch (Exception $e)
				{
					$response=$this->raiseLog(JText::_('LOG_GET_STATUS_FAIL_TWITTER'),$e->getMessage(),$oauth_key->user_id,1);

				}
				$content=json_decode($tmhOAuth->response['response'],true);

				$data=$this->renderstatus($content);
				if(empty($data))
		 		 continue;
				if($data)
				{
					$returndata[$i]['user_id'] = $oauth_key->user_id;
					$returndata[$i]['status']	 = $data;
					$i++;
					$this->raiseLog(JText::_('LOG_GET_STATUS_SUCCESS'),JText::_('LOG_GET_STATUS'),$oauth_key->user_id,1);
				}
				else
				{

					$this->raiseLog(JText::_('LOG_GET_STATUS_FAIL'),JText::_('LOG_GET_STATUS'),$oauth_key->user_id,1);
				}

		}

		if(!empty($returndata['0']))
		return $returndata;
		else
		return;

	}*/
}
