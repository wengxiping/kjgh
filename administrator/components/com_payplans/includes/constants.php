<?php
/**
* @package      PayPlans
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Since 3.7.0
define('PP_JOOMLA', JPATH_ROOT);
define('PP_ID', 'com_payplans');
define('PP_ADMIN', JPATH_ADMINISTRATOR . '/components/' . PP_ID);
define('PP_ADMIN_UPDATES', PP_ADMIN . '/updates');
define('PP_SITE', JPATH_ROOT . '/components/' . PP_ID);
define('PP_LIB', PP_ADMIN . '/includes');
define('PP_MODELS', PP_ADMIN . '/models');
define('PP_DEFAULTS', PP_ADMIN . '/defaults');
define('PP_FORMS', PP_DEFAULTS . '/forms');
define('PP_MEDIA', PP_JOOMLA . '/media/' . PP_ID);
define('PP_THEMES', PP_SITE . '/themes');
define('PP_SCRIPTS', PP_MEDIA . '/scripts');
define('PP_APPS_REPO', PP_DEFAULTS . '/repository');
define('PP_DOWNLOADS', PP_MEDIA . '/downloads');

define('PP_SESSION_NAMESPACE', PP_ID);

// Color states for info messages
define('PP_MSG_SUCCESS', 'success');
define('PP_MSG_WARNING', 'warning');
define('PP_MSG_ERROR', 'error');
define('PP_MSG_INFO', 'info');

define('PP_STATE_PUBLISHED', 1);
define('PP_STATE_UNPUBLISHED', 0);

// Language server
define('PP_LANGUAGES_SERVER', 'https://services.stackideas.com/translations/payplans');
define('PP_LANGUAGES_INSTALLED', 1);
define('PP_LANGUAGES_NOT_INSTALLED', 0);
define('PP_LANGUAGES_NEEDS_UPDATING', 3);

// Services Server
define('PP_SERVICE_NEWS', 'https://stackideas.com/updater/manifests/payplans');
define('PP_SERVICE_VERSION', 'https://stackideas.com/updater/manifests/payplans');
define('PP_JUPDATE_SERVICE', 'https://stackideas.com/jupdates/manifest/payplans');

// Int and Float values
define('PP_ZERO', floatval(0));
define('PP_CACERT', PP_LIB . '/connector/cacert.pem');

// Configuration Keys
define('PP_CONFIG_BASIC', 	1);
define('PP_CONFIG_ADVANCE', 	2);
define('PP_CONFIG_INVOICE',   3);
define('PP_CONFIG_EXPERT',    4);
define('PP_CONFIG_CUSTOMIZATION', 5);
define('PP_CONFIG_CRONFREQUENCY_DIVIDER', 5);
define('PP_INSTANCE_REQUIRE', true);

// Constants for statistics
define('PP_STATS_DURATION_DAILY', 101);
define('PP_STATS_DURATION_WEEKLY', 102);
define('PP_STATS_DURATION_MONTHLY', 103);
define('PP_STATS_DURATION_YEARLY', 104);
define('PP_STATS_DURATION_CUSTOM', 105);
define('PP_STATS_DURATION_LIFETIME', 106);
define('PP_STATS_DURATION_LAST_30_DAYS', 107);

// GDPR
define('PP_DOWNLOAD_REQ_NEW', 0);
define('PP_DOWNLOAD_REQ_LOCKED', 1);
define('PP_DOWNLOAD_REQ_PROCESS', 2);
define('PP_DOWNLOAD_REQ_READY', 3);

///////////////////////////////////////////
// All codes below are legacy codes prior to 3.7.0
///////////////////////////////////////////
//define site path
// require_once(PP_SITE . '/includes/defines.php');

//all folder paths
// define('PAYPLANS_PATH_CONTROLLER_ADMIN', PP_ADMIN . '/controllers');
// define('PAYPLANS_PATH_VIEW_ADMIN', PP_ADMIN . '/views');
// define('PAYPLANS_PATH_INCLUDE_ADMIN', PP_ADMIN . '/includes');
// define('PAYPLANS_PATH_TEMPLATE_ADMIN', PP_ADMIN . '/templates');
// define('PAYPLANS_PATH_INSTALLER_ADMIN', PP_ADMIN . '/installer');



define('PP_STATISTICS_TYPE_ALL', 'all');
define('PP_STATISTICS_TYPE_SALES', 'sales');
define('PP_STATISTICS_TYPE_REVENUE', 'revenue');
define('PP_STATISTICS_TYPE_RENEWALS', 'renewals');
define('PP_STATISTICS_TYPE_UPGRADES', 'upgrades');
define('PP_STATISTICS_TYPE_GROWTH', 'growth');

// Empty status
define('PP_NONE', 0);

// Subscription status
define('PP_SUBSCRIPTION_ACTIVE', 1601);
define('PP_SUBSCRIPTION_HOLD', 1602);
define('PP_SUBSCRIPTION_EXPIRED', 1603);
define('PP_SUBSCRIPTION_NONE', 0);
define('PP_SUBSCRIPTION_LIFETIME', '000000000000');

// Order status
define('PP_ORDER_CONFIRMED', 301);
define('PP_ORDER_PAID', 302);   // Un-used later 1.4
define('PP_ORDER_COMPLETE', 303);
define('PP_ORDER_HOLD', 304);
define('PP_ORDER_EXPIRED', 305);
define('PP_ORDER_CANCEL', 306);

// Invoice status
define('PP_INVOICE_CONFIRMED', 401);
define('PP_INVOICE_PAID', 402);
define('PP_INVOICE_REFUNDED', 403);
define('PP_INVOICE_WALLET_RECHARGE', 404);

// Price types
define('PP_PRICE_FIXED', 99);
define('PP_PRICE_RECURRING', 100);
define('PP_PRICE_RECURRING_TRIAL_1', 101);
define('PP_PRICE_RECURRING_TRIAL_2', 102);

// Recurring types
define('PP_RECURRING', 'recurring');
define('PP_RECURRING_TRIAL_1', 'recurring_trial_1');
define('PP_RECURRING_TRIAL_2', 'recurring_trial_2');

// Logs
define('PP_LOGS_FOLDER_MAXSIZE', 32768);

// Discounts
define('PP_DISCOUNTS_CODE_SIZE', 6);
define('PP_PRODISCOUNT_EACHRECURRING','eachrecurring');
define('PP_PRODISCOUNT_AUTOONUPGRADE','autodiscount_onupgrade');
define('PP_PRODISCOUNT_AUTOONRENEWAL','autodiscount_onrenewal');
define('PP_PRODISCOUNT_UPGRADE_DISCOUNT','upgradeDiscount');
define('PP_PRODISCOUNT_RENEWAL_DISCOUNT','renewalDiscount');
define('PP_PRODISCOUNT_AUTO_ON_INVOICE_CREATION','autodiscount_oninvoicecreation');
define('PP_PRODISCOUNT_INVOICE_CREATION_DISCOUNT','invoiceCeationDiscount');
define('PP_PRODISCOUNT_EXTEND_TIME_DISCOUNT','discount_for_time_extend');

// Mapping class
define('PP_RETRIEVE_MAPPING_CLASS', true);

/**
 * Discountable Modifier means any addition or substraction
 * which should be applied before discount and tax are being applied
 *
 * FIXED amount will be applied before PERCENTAGE amount
 */
define('PP_MODIFIER_FIXED_DISCOUNTABLE', 10);
define('PP_MODIFIER_PERCENT_DISCOUNTABLE', 15);

/**
 * Same as PERCENT_DISCOUNTABLE but only difference is that % will be calculated on subtotal of invoice, instead of total
 */
define('PP_MODIFIER_PERCENT_OF_SUBTOTAL_DISCOUNTABLE', 17);

/**
 * Discount Modifier means discount on order/invocie
 * which should be applied after Discountable modifier
 *
 * FIXED discount will be applied before PERCENTAGE discount
 */
define('PP_MODIFIER_FIXED_DISCOUNT', 20);
define('PP_MODIFIER_PERCENT_DISCOUNT', 25);

/**
 * Taxable Modifier means tax on order/invocie
 * which should be applied after Discount modifier
 * It consist of value on which discount should not be applied
 * but tax should be there.
 * FIXED tax will be applied before PERCENTAGE tax
 */
define('PP_MODIFIER_PERCENT_TAXABLE', 27);

/**
 * Same as PERCENT_TAXABLE but only difference is that % will be calculated on subtotal of invoice, instead of total
 */
define('PP_MODIFIER_PERCENT_OF_SUBTOTAL_TAXABLE', 28);

/**
 * Tax Modifier means tax on order/invocie
 * which should be applied after Discount modifier
 *
 * FIXED tax will be applied before PERCENTAGE tax
 */
define('PP_MODIFIER_FIXED_TAX', 30);
define('PP_MODIFIER_PERCENT_TAX', 35);

/**
 * TAXABLE Modifier means any addition or substraction
 * which should be applied after applying discount and tax
 *
 * FIXED amount will be applied before PERCENTAGE amount
 */
define('PP_MODIFIER_FIXED_NON_TAXABLE', 22);
define('PP_MODIFIER_PERCENT_NON_TAXABLE', 45);
define('PP_MODIFIER_FIXED_NON_TAXABLE_TAX_ADJUSTABLE', 49);

/**
 * Same as PERCENT_NON_TAXABLE but only difference is that % will be calculated on subtotal of invoice, instead of total
 */
define('PP_MODIFIER_PERCENT_OF_SUBTOTAL_NON_TAXABLE', 50);

/**
 * Constants for frequency of modifire on invoice
 */
define('PP_MODIFIER_FREQUENCY_ONE_TIME', 'ONE TIME');
define('PP_MODIFIER_FREQUENCY_EACH_TIME', 'EACH TIME');

/*
* Constant used in addon apps
*/
define('PP_PLANADDONS_MODIFIER', 'plan_addons');
define('PP_PLANADDONS_ONETIME', 501);
define('PP_PLANADDONS_EACHRECURRING', 502);

define('PP_PLANADDONS_STAT_PENDING', 0);
define('PP_PLANADDONS_STAT_WORKING', 1);
define('PP_PLANADDONS_STAT_PROCESSED', 2);

define('PP_CONST_NONE', 0);
define('PP_CONST_ALL', -1);
define('PP_CONST_ANY', -2);

/*
 * Constants used in EU VAT aoo
 */
define('PP_EUVAT_PURPOSE_NONE', 0);
define('PP_EUVAT_PURPOSE_PERSONAL', 1);
define('PP_EUVAT_PURPOSE_BUSINESS', 2);



