<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
if(defined('_JEXEC')===false) die();
?>

<div class="pp-selected-plan">
	<fieldset class="form-horizontal">
		<legend>
			<h4><?php echo JText::_('COM_PAYPLANS_PLAN_SELECTED_PLAN'); ?></h4>
		</legend>
		<?php $plan_grid_class = ""; echo $this->output('site/plan/default/plan',compact('plan','plan_grid_class'));?>
		<input type="hidden" name="plan_id" id="payplans_subscription_plan" value="<?php echo $plan->getId();?>" />
	</fieldset>
</div>
