<?php

/*------------------------------------------------------------------------
# plg_affiliate_tracker - Affiliate Tracker core plugin
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2013 JoomlaThat.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaThat.com
# Technical Support:	Forum - http://www.JoomlaThat.com/forum
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.database.table' );

//new for Joomla 3.0
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

class  plgSystemAffiliate_tracker extends JPlugin
{

	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

	}

	function onAfterInitialise()
	{
		$compVersion = $this->checkComponentInstalled('com_affiliatetracker');

		if (empty($compVersion)) return false;

		$mainframe = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		$id = JRequest::getInt('atid', 0, 'get' );
		$valid = null;

		if (!empty($id)) {
			$query = $db->getQuery(true);
			$query->select($db->qn('id'))
						->from($db->qn('#__affiliate_tracker_accounts'))
						->where($db->qn('id') . ' = '. $db->q($db->escape($id)))
						->where($db->qn('publish') . ' = ' . $db->q(1));
			$db->setQuery($query);
			$valid = $db->loadResult();
		} else {
			$uri = JFactory::getURI();
			$base = $uri->base();
			$current = $uri->current();

			$theword = str_replace($base, "", $current);

			if (!empty($theword) && (version_compare($compVersion, '1.1.0') >= 0)) {
				$query = $db->getQuery(true);
				$query->select($db->qn('id'))
							->from($db->qn('#__affiliate_tracker_accounts'))
							->where($db->qn('ref_word') . ' = '. $db->q($db->escape($theword)))
							->where($db->qn('publish') . ' = ' . $db->q(1));
				$db->setQuery($query);
				$valid = $db->loadResult();
				$id = $valid;
			}

			if (empty($valid) && !empty($_SERVER['HTTP_REFERER']) && (version_compare($compVersion, '1.2.0') >= 0)) {
				$realRefer = $_SERVER['HTTP_REFERER'];
				if (!empty($realRefer)) {
					$query = $db->getQuery(true);
					$query->select($db->qn('id'))
								->from($db->qn('#__affiliate_tracker_accounts'))
								->where($db->qn('refer_url') . ' LIKE '. $db->q($db->escape('%'.parse_url($realRefer, PHP_URL_HOST).'%')))
								->where($db->qn('publish') . ' = ' . $db->q(1));
					$db->setQuery($query);

					$valid = $db->loadResult();
					$id = $valid;
				}
			}
		}

		if ($id && $valid && $mainframe->isSite())
		{
			$this->log_data("First step: ".$id ) ;
			$store = $this->set_cookie_af( $id );

			if($store['isnew']){
				 $this->saveLog( $store );
			}
		}
		elseif (!empty($_COOKIE["atid"]) && $user->id) {
			// if the cookie is available and the user is logged in, we check if there is a log linked to that user

			$cookiecontent = unserialize(base64_decode($_COOKIE["atid"]));
			$atid = (int)$cookiecontent["atid"] ;

			$query = $db->getQuery(true);
			$query->select($db->qn('id'))
						->from($db->qn('#__affiliate_tracker_logs'))
						->where($db->qn('user_id') . ' = '. $db->q($db->escape($user->id)))
						->where($db->qn('atid') . ' = ' . $db->q($db->escape($atid)));
			$db->setQuery($query);
			$exists = $db->loadResult();

			if(!$exists){
				// the record linking the atid with the user id doesn't exist. we create a new log
				$data['atid'] = $atid ;
				$this->saveLog( $data );
			}
		}

		return true;

	}


	function set_cookie_af($id){

		//Set cookie
		if(empty($_COOKIE["atid"])){

			$store = array();
			$store['atid'] = $id ;

			$data = base64_encode(serialize($store));

			setcookie("atid", $data, time()+3600*24*$this->params->get('days', 30), "/"); //set cookie

			$uri = JFactory::getURI();
			//$this->log_data("Setting cookie: ".$id. " IP: ".$_SERVER['REMOTE_ADDR'] ." refer: ".$_SERVER['HTTP_REFERER']. " URI: ". $uri->toString());
			//$this->log_data("Setting cookie (store): ");
			$this->log_data($store);
			$this->log_data($data);

			$store['isnew'] = true ;


		}else{
		  	$store = unserialize(base64_decode($_COOKIE["atid"]));
		  	$store['isnew'] = false ;

		}

		return $store;
	}


	function saveLog($data){

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$row = new TableAffiliateTrackerLog($db);

		if($data['atid']){
			// Bind the form fields to the statistics table

			if (!$row->bind($data)) {
				$mainframe->enqueueMessage($db->getErrorMsg());
				return false;
			}

			if (!$row->check()) {
				$mainframe->enqueueMessage($db->getErrorMsg());
				return false;
			}

			if (!$row->store()) {
				$mainframe->enqueueMessage($db->getErrorMsg());
				return false;
			}
		}

	}

	function log_data($data)
	{
		/*
		$f = fopen(JPATH_SITE . DS . 'cache' . DS . 'affiliates.txt', 'a');
		fwrite($f, "\n" . date('F j, Y, g:i a') . "\n");
		fwrite($f, print_r($data, true));
		fclose($f);
		*/
	}

	private function checkComponentInstalled($compName) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('manifest_cache')
					->from($db->qn('#__extensions'))
					->where('element = "' . $compName . '"');

		$db->setQuery($query);

		$manifest = json_decode($db->loadResult(), true);
		return $manifest['version'];

	}

}

class TableAffiliateTrackerLog extends JTable
{

	var $id = null;
	var $datetime = null;
	var $sessionid = null;
	var $atid = null;
	var $account_id = null;
	var $refer = null;
	var $ip = null;
	var $user_id = null;

	function __construct(& $db) {
		parent::__construct('#__affiliate_tracker_logs', 'id', $db);
	}

	function check(){

		$user = JFactory::getUser();

		$this->ip = $_SERVER['REMOTE_ADDR'] ;
		if(isset($_SERVER['HTTP_REFERER'])) $this->refer = $_SERVER['HTTP_REFERER'] ;
		if(!$this->user_id) $this->user_id = $user->id ;

		$this->account_id = $this->atid ;

		$session = JFactory::getSession();
    $this->sessionid = $session->getId();

		$this->datetime = date('Y-m-d H:i:s') ;

		return true;
	}

}
