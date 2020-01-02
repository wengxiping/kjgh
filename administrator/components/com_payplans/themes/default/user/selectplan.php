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

<div class="pp-user-selectplan">
<form action="<?php //echo $uri; ?>" method="post" name="selectPlanForm" id="selectPlanForm">
	<div class="pp-user-selectplan-message center">
		<?php echo JText::_('COM_PAYPLANS_USER_APPLY_PLAN_HELP_MESSAGE');?>
	</div>
	<div class="center pp-gap-top20">
		<?php echo PayplansHtml::_('plans.edit', 'plan_id', '', array('none'=>true));?>
	</div>
</form>
</div>
<?php 
