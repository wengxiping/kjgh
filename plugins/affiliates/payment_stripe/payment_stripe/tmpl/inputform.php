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

defined('_JEXEC') or die('Restricted access'); ?>

<div class="control-group">
    <label class="control-label" for="payment_stripe_secret_key"> <?php echo JText::_( 'SECRET_KEY' ); ?></label>
    <div class="controls">
        <input class="inputbox" type="text" name="payment_options[payment_stripe][secret_key]" id="payment_stripe_secret_key" size="80" maxlength="250" value="<?php if (isset($vars->payment_stripe)) echo $vars->payment_stripe->secret_key; ?>" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="payment_stripe_publishable_key"> <?php echo JText::_( 'PUBLISHABLE_KEY' ); ?></label>
    <div class="controls">
        <input class="inputbox" type="text" name="payment_options[payment_stripe][publishable_key]" id="payment_stripe_publishable_key" size="80" maxlength="250" value="<?php if (isset($vars->payment_stripe)) echo $vars->payment_stripe->publishable_key; ?>" />
    </div>
</div>