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

// Please change the value of the following lines according to your environment

// MerchantId provided by Netaxept
$merchantId = "Chamge me in Parameters.php";

// Token provided by Netaxept
$token = "Chamge me in Parameters.php";

// WSDL location found in documentation
$wsdl="https://epayment-test.bbs.no/Netaxept.svc?wsdl";

// Redirect from Netaxept to your site
$path_parts = pathinfo($_SERVER["PHP_SELF"]);
// this is an example is showing how to add one (or several additional parameters on the terminal)
// $redirect_url = "http://" . $_SERVER["HTTP_HOST"] . $path_parts['dirname'] . "/Process.php?webshopParameter=1234567";
$redirect_url = "http://" . $_SERVER["HTTP_HOST"] . $path_parts['dirname'] . "/Process.php";

// Netaxept Terminal Location
$terminal = "https://epayment-test.bbs.no/terminal/default.aspx";