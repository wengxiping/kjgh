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
 * The AuthorizeNet PHP SDK. Include this file in your project.
 *
 * @package AuthorizeNet
 */
require(__DIR__ . '/shared/AuthorizeNetRequest.php');
require(__DIR__ . '/shared/AuthorizeNetTypes.php');
require(__DIR__ . '/shared/AuthorizeNetXMLResponse.php');
require(__DIR__ . '/shared/AuthorizeNetResponse.php');
require(__DIR__ . '/AuthorizeNetAIM.php');
require(__DIR__ . '/AuthorizeNetARB.php');
require(__DIR__ . '/AuthorizeNetCIM.php');
require(__DIR__ . '/AuthorizeNetSIM.php');
require(__DIR__ . '/AuthorizeNetDPM.php');
require(__DIR__ . '/AuthorizeNetTD.php');
require(__DIR__ . '/AuthorizeNetCP.php');

if (class_exists("SoapClient")) {
	require(__DIR__ . '/AuthorizeNetSOAP.php');
}

/**
 * Exception class for AuthorizeNet PHP SDK.
 *
 * @package AuthorizeNet
 */
class AuthorizeNetException extends Exception
{
}