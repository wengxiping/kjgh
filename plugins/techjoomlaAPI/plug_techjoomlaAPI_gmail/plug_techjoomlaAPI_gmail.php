<?php
/*
	* @package Gmail plugin for Invitex
	* @copyright Copyright (C)2010-2011 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link http://www.techjoomla.com
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');


	$lang = JFactory::getLanguage();
	$lang->load('plug_techjoomlaAPI_gmail', JPATH_ADMINISTRATOR);
	class plgTechjoomlaAPIplug_techjoomlaAPI_gmail extends JPlugin
	{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$appKey	= $this->params->get('appKey');
		$appSecret	= $this->params->get('appSecret');
		$this->callbackUrl=JURI::root().'techjoomlaApi_gmail.php';
		$this->errorlogfile='gmail_error_log.php';
		$this->user = JFactory::getUser();

		$this->db=JFactory::getDBO();
		$this->API_CONFIG=array(
		'clientid'       => trim($appKey),
		'clientsecret'    => trim($appSecret),
		'redirecturi' 		 => $this->callbackUrl
		);

	}

	/*
		 * Get the plugin output as a separate html form
     *
     * @return  string  The html form for this plugin
     * NOTE: all hidden inputs returned are very important
	*/
	function renderPluginHTML($config)
	{

		$plug=array();
		$plug['name']="Gmail";
  	//check if keys are set
		if($this->API_CONFIG['clientid']=='' || $this->API_CONFIG['clientsecret']=='' || !in_array($this->_name,$config))
		{
			$plug['error_message']=true;
			return $plug;
		}
		$plug['api_used']=$this->_name;
		$plug['message_type']='email';
		$plug['img_file_name']="gmail.png";
		if(isset($config['client']))
		$client=$config['client'];
		else
		$client='';
		$plug['apistatus'] = $this->connectionstatus($client);
		return $plug;
	}

	function connectionstatus($client=''){
		$where='';
		if($client)
		$where=" AND client='".$client."'";
	 	$query 	= "SELECT token FROM #__techjoomlaAPI_users WHERE token<>'' AND user_id = {$this->user->id}  AND api='{$this->_name}'".$where;
		$this->db->setQuery($query);
		$result	= $this->db->loadResult();
		if ($result)
			return 1;
		else
			return 0;
	}

	function get_request_token($callback)
	{

		header('Location:'."https://accounts.google.com/o/oauth2/auth?client_id=".$this->API_CONFIG['clientid']."&redirect_uri=".$this->API_CONFIG['redirecturi']."&scope=https://www.googleapis.com/auth/contacts.readonly&response_type=code");
		return true;
	}

	function store($client,$data) #TODO insert client also in db
	{

	 	$qry = "SELECT id FROM #__techjoomlaAPI_users WHERE user_id ={$this->user->id} AND client='{$client}' AND api='{$this->_name}' ";
		$this->db->setQuery($qry);
		$id	=$exists = $this->db->loadResult();
		$row = new stdClass;
		$row->id=NULL;
		$row->user_id = $this->user->id;
		$row->api 		= $this->_name;
		$row->client=$client;
		$row->token=json_encode($data);

		if($exists)
		 {

		 		$row->id=$id;
	 			$this->db->updateObject('#__techjoomlaAPI_users', $row, 'id');
		 }
		 else
		 {

				$status=$this->db->insertObject('#__techjoomlaAPI_users', $row);
		 }

	}

	function getToken($user=''){
		$user=$this->user->id;
		$where = '';
		if($user)
			$where = ' AND user_id='.$user;

		$query = "SELECT user_id,token
		FROM #__techjoomlaAPI_users
		WHERE token<>'' AND api='{$this->_name}' ".$where ;
		$this->db->setQuery($query);
		return $this->db->loadObjectlist();
	}
	function remove_token($client)
	{
		if($client!='')
		$where="AND client='{$client}' AND api='{$this->_name}'";

		#TODO add condition for client also
		$qry 	= "UPDATE #__techjoomlaAPI_users SET token='' WHERE user_id = {$this->user->id} ".$where;
		$this->db->setQuery($qry);
		$this->db->query();
	}

	function plug_techjoomlaAPI_gmailget_contacts($offset='',$limit='',$get)
	{
			$accesstoken=$authcode='';
			if(isset($get['code']))
				$authcode		= $get['code'];
			$fields=array(
			'code'=>  urlencode($authcode),
			'client_id'=>  urlencode($this->API_CONFIG['clientid']),
			'client_secret'=>  urlencode($this->API_CONFIG['clientsecret']),
			'redirect_uri'=>  urlencode($this->API_CONFIG['redirecturi']),
			'grant_type'=>  urlencode('authorization_code') );

			//url-ify the data for the POST

			$fields_string = '';

			foreach($fields as $key=>$value)
			{
				$fields_string .= $key.'='.$value.'&';
			}
			$fields_string	=	rtrim($fields_string,'&');


            $gmail_connection_url	=	'https://accounts.google.com/o/oauth2/token';

			$result	=	$this->get_url_contentsByCURL($gmail_connection_url,$fields_string);

			//extracting access_token from response string
			$response   = new stdClass();
			$response   =  json_decode($result);


			if(property_exists($response, "access_token")){
            //passing accesstoken to obtain contact details
			$gmail_contacts_url	=	'https://www.google.com/m8/feeds/contacts/default/full?max-results=9999&oauth_token='. $response->access_token;
			$xmlresponse	=	$this->get_url_contentsByCURL($gmail_contacts_url);


			$xml=  new SimpleXMLElement($xmlresponse);
			if(property_exists($xml, "error")){

				$this->raiseException($xmlresponse,$userid,$display,$params);
				return false;
			}
			$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

			$result_emails = $xml->xpath('//gd:email');
			$connections=$this->renderContacts($result_emails);

			return $connections;
			}
			else{
					if(isset($response->error))
					{
						if($response->error=='invalid client')
						{
							JFactory::getApplication()->enqueueMessage('GMAIL-APP-Get Contacts-Error:'.$response->error, 'warning');
						}
					}
					$this->raiseException($response,$userid,$display,$params);
					return false;
			}
	}

	function renderContacts($result_emails)
	{
		$r_connections=array();
		$count=0;
		foreach($result_emails as $ind=>$conn)
		{
			if($conn->attributes()->address)
			{
				$r_connections[$count]=new stdClass();
				$r_connections[$count]->id = (string)$conn->attributes()->address;
				$r_connections[$count]->name ='';
				$r_connections[$count]->picture_url ='';
			}
			else
			continue;
			$count++;

		}
		return $r_connections;
	}

	function plug_techjoomlaAPI_gmailget_profile()
	{

  }
	function plug_techjoomlaAPI_gmailsend_message($post)
	{

	}

	function plug_techjoomlaAPI_gmailgetstatus()
	{

	}
	function plug_techjoomlaAPI_gmailsetstatus($userid,$originalContent,$comment,$attachment='')
	{
	}
	function get_url_contentsByCURL($url,$fields_string=''){
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL,$url);

		if(!empty($fields_string)){
			curl_setopt($ch,CURLOPT_POST,5);
                        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
                }
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set so curl_exec returns the result instead of outputting it.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //to trust any ssl certificates
		$ret = curl_exec($ch);
		curl_close($ch);
              return $ret;
	}

	function raiseException($exception,$userid='',$display=1,$params=array())
	{
		$path="";
		$params['name']=$this->_name;
		$params['group']=$this->_type;
		$loghelperobj=	new techjoomlaHelperLogs();
		$loghelperobj->simpleLog($exception,$userid,'plugin',$this->errorlogfile,$path,$display,$params);
		return;
	}

	function raiseLog($status_log,$desc="",$userid="",$display="")
	{

		$params=array();
		$params['desc']	=	$desc;
		if(is_object($status))
		$status=JArrayHelper::fromObject($status_log,true);

		if(is_array($status))
		{
			if(isset($status['info']['http_code']))
			{
				$params['http_code']		=	$status['info']['http_code'];
				if(!$status['success'])
				{
						if(isset($status['gmail']))
							$response_error=techjoomlaHelperLogs::xml2array($status['gmail']);


					$params['success']			=	false;
					$this->raiseException($response_error['error']['message'],$userid,$display,$params);
					return false;

				}
				else
				{
					$params['success']	=	true;
					$this->raiseException(JText::_('LOG_SUCCESS'),$userid,$display,$params);
					return true;

				}

			}
		}
		$this->raiseException(JText::_('LOG_SUCCESS'),$userid,$display,$params);
		return true;
	}

}//end class
