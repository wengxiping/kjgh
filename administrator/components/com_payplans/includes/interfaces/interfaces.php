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

require_once(__DIR__ . '/apptriggerable.php');
require_once(__DIR__ . '/discountable.php');
require_once(__DIR__ . '/maskable.php');
require_once(__DIR__ . '/api/payment.php');
require_once(__DIR__ . '/api/invoice.php');
require_once(__DIR__ . '/api/plan.php');
require_once(__DIR__ . '/api/transaction.php');
require_once(__DIR__ . '/api/subscription.php');

// App Interfaces
require_once(__DIR__ . '/app/discount.php');