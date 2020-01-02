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
?>
<style>
	#pp .wpwl-form.wpwl-form-card { 
		box-shadow: none; 
		width: 100% !important; 
		max-width: none;
	}
	#pp .wpwl-control {
		height: 27.5px;
	}
</style>

<script src="<?php echo $src;?>v1/paymentWidgets.js?checkoutId=<?php echo $checkoutId;?>"></script>

<form action="<?php echo $postUrl?>" class="paymentWidgets" data-payunity-form>VISA MASTER PAYPAL</form>
		
