<?php

/*------------------------------------------------------------------------
# com_affiliatetracker - Affiliate Tracker for Joomla
# ------------------------------------------------------------------------
# author				Germinal Camps
# copyright 			Copyright (C) 2014 JoomlaThat.com. All Rights Reserved.
# @license				http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: 			http://www.JoomlaThat.com
# Technical Support:	Forum - http://www.JoomlaThat.com/support
-------------------------------------------------------------------------*/

defined('_JEXEC') or die('Restricted access');

if(!defined('DS')){
    define('DS',DIRECTORY_SEPARATOR);
}

require_once(JPATH_SITE . DS . 'plugins' . DS . 'affiliates' . DS . 'payment_stripe' . DS . 'config.php');

if ( ! function_exists('plg_affiliates_escape')) {

    function plg_affiliates_escape($var)
    {
        return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
    }
}
?>

<table class="userlist">
    <tbody>
    <tr>
        <td class="title">
            <form action="<?php echo plg_affiliates_escape($vars->action_url) ?>" method="post">
                <script src="https://checkout.stripe.com/checkout.js"
                        class="stripe-button"
                        data-key="<?php echo $stripe['publishable_key']; ?>"
                        data-amount="<?php echo plg_affiliates_escape($vars->row->payment_amount)*100 ?>"
                        data-description="<?php echo plg_affiliates_escape($vars->row->payment_description) ?>"
                        data-currency="<?php echo $vars->currency; ?>"
                        ></script>
                <input type="hidden" name="item_number" value="<?php echo plg_affiliates_escape($vars->row->id) ?>" />
                <input type="hidden" name="amount" value="<?php echo plg_affiliates_escape($vars->row->payment_amount) ?>" />
            </form>
        </td>
        <td class="input">
            <?php echo plg_affiliates_escape($vars->note); ?>
        </td>
    </tr>
    </tbody>
</table>
