<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="pp-recurr-validation row-fluid">
			<div class="pp-bold pp-primary pp-color clearfix">
				<div class="span2">
					<?php echo JText::_('COM_PP_PLAN_EDIT_RECURRENCE_APP_NAME');?>
				</div>
				<div class="span2">
					<?php echo JText::_('COM_PP_PLAN_EDIT_RECURRENCE_UNIT');?>
				</div>
				<div class="span2">
					<?php echo JText::_('COM_PAYPLANS_PLANS_EDIT_RECURRENCE_PERIOD');?>
				</div>
				<div class="span2">
					<?php echo JText::_('COM_PAYPLANS_PLANS_EDIT_RECURRENCE_COUNT');?>
				</div>
				<div class="span4">
					<?php echo JText::_('COM_PAYPLANS_PLANS_EDIT_RECURRING_MESSAGE');?>
				</div>
			</div>
		<?php foreach($time as $app => $recurringTime) : ?>
			<div class="pp-recurr-validation-value pp-secondary pp-border pp-color  row-fluid clearfix">
				<div class="span2">
					<?php echo $app."\t"; ?>
				</div>
				<div class="span2">
					<?php 	echo $recurringTime['period']."\t"; ?>
				</div>
				<div class="span2">
					<?php echo $recurringTime['unit']."\t";	?>
				</div>
				<div class="span2">
					<?php echo $recurringTime['frequency']."\t";	?>
				</div>
				<div class="span4">
					<?php if(isset($recurringTime['message'])){echo $recurringTime['message']."\t";} ?>
				</div>
			</div>
		<?php endforeach;?>
		<div class="pp-recurr-validation-value row-fluid">
				<h6><?php echo JText::_('COM_PAYPLANS_PLANS_EDIT_RECURRENCE_COUNT_MSG');?></h6>
				<code><strong><?php echo JText::_('COM_PAYPLANS_NA')?></strong></code>&nbsp;&nbsp;<span ><?php echo JText::_('COM_PAYPLANS_NOT_APPLICABLE');?></span>

		</div>
</div>
<?php

