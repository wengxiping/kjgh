<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	20 December 2016
 * @file name	:	controllers/ajax.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class JblanceControllerAjax extends JControllerAdmin {

	function __construct(){
		parent::__construct();
	}

	function dzUploadFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		JBMediaHelper::dzUploadFile();
	}

	function dzRemoveFile(){
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		JBMediaHelper::dzRemoveFile();
	}

}
