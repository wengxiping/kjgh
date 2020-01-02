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
?>
<script src="https://js.stripe.com/v2/"></script>

<div class="o-btn-group">
	<a href="javascript:void(0);" class="btn btn-pp-default-o" data-stripe-update-<?php echo $uid;?>>
		<i class="far fa-credit-card"></i>&nbsp; <?php echo JText::_('COM_PP_UPDATE_BILLING');?>
	</a>
</div>