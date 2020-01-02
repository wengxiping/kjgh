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
<dialog>
	<width>920</width>
	<height>450</height>
	<selectors type="json">
	{
		"{form}" : "[data-form]",
		"{cancelButton}"  : "[data-cancel-button]",
		"{startButton}" : "[data-start-button]",
		"{confirmationInfo}" : "[data-start-confirmation]",
		"{progressWrapper}" : "[data-start-progress]",
		"{progressBar}" : "[data-progress-bar]",
		"{finishInfo}" : "[data-build-info-finish]",
		"{progressInfo}" : "[data-build-info-progress]",
		"{progressCounter}" : "[data-progress-counter]"
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
	<title><?php echo JText::_('COM_PP_DIALOG_REBUILD_STAT'); ?></title>
	<content>
		<form method="post" data-form>
			<div class="row-fluid text-center t-lg-mb--lg" data-start-confirmation>
				<p><?php echo JText::_('COM_PAYPLANS_DASHBOARD_REBUILD_START_MSG'); ?></p>
				<p><?php echo JText::_('COM_PAYPLANS_DASHBOARD_REBUILD_START_MSG_NOTE'); ?></p>
				<p class="t-lg-mt--lg"><strong><?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_REBUILD_BEFORE_START_MSG_NUMBER_OF_DAYS_STRING', $totalDays); ?></strong></p>
			</div>

			<div data-start-progress class="t-hidden">
				<div class="row-fluid text-center t-lg-mt--lg">
					<div class="o-alert o-alert--warning t-lg-mb--lg" data-build-info-progress><?php echo JText::_('COM_PAYPLANS_DASHBOARD_REBUILD_MSG_DONOT_CLOSE');?></div>
					<div class="o-alert o-alert--success t-lg-mb--lg t-hidden" data-build-info-finish><p><?php echo JText::_('Rebuild process completed'); ?></p></div>

					<div class="o-progress">
						<div class="o-progress-bar o-progress-bar--info" style="width: 20%" data-progress-bar></div>
					</div>
					
					<div class="">
						<span id="pp-rebuild-progress-count" data-progress-counter>
							<?php echo JText::sprintf('COM_PAYPLANS_DASHBOARD_REBUILD_PROGRESS', 0, $totalDays);?>
						</span>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'analytics', 'rebuildStat'); ?>
			<input type="hidden" name="totalDays" value="<?php echo $totalDays; ?>" data-total-days />
			<input type="hidden" name="rebuildLimit" value="<?php echo $rebuildLimit; ?>" data-rebuild-limit />
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-pp-warning btn-sm"><?php echo JText::_('COM_PAYPLANS_AJAX_CLOSE_BUTTON'); ?></button>
		<button data-start-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PAYPLANS_JS_DASHBOARD_REBUILD_START_DIALOG_TITLE'); ?></button>
	</buttons>
</dialog>
