<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* PayPlans is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>800</width>
	<height>200</height>
	<selectors type="json">
	{
		"{cancelButton}"  : "[data-cancel-button]",
		"{applyButton}" : "[data-submit-button]",
		"{form}" : "[data-form]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function()
		{
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_( 'COM_PAYPLANS_AJAX_APPLY_PLAN' ); ?></title>
	<content>
		<form method="post" name="selectPlanForm" id="selectPlanForm" data-form>
			<p>
				<div class="pp-user-selectplan-message center">
					<?php echo JText::_('COM_PAYPLANS_USER_APPLY_PLAN_HELP_MESSAGE');?>
				</div>
				<div class="center pp-gap-top20">
					<?php echo PayplansHtml::_('plans.edit', 'plan_id', '', array('none'=>true));?>
				</div>
			</p>
			<input type="hidden" name="option" value="com_payplans" />
			<input type="hidden" name="controller" value="user" />
			<input type="hidden" name="task" value="applyPlan" />
			<input type="hidden" name="ids" value="<?php echo implode(',',$cid); ?>" />
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PAYPLANS_AJAX_CANCEL_BUTTON'); ?></button>
		<button data-submit-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PAYPLANS_AJAX_APPLY_BUTTON'); ?></button>
	</buttons>
</dialog>
