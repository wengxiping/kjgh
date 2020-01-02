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
	<width>960</width>
	<height>600</height>
	<selectors type="json">
	{
		"{closeButton}" : "[data-close-button]",
		"{submitButton}" : "[data-submit-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::sprintf('COM_PP_VIEWING_LOG_DIALOG_TITLE', $logId); ?></title>
	<content>
		<?php if (is_array($data)) { ?>
			<div class="row-fluid">
				<table class="app-table table table-pp">
					<thead>
						<tr>
							<th width="14%">
								<?php echo JText::_('COM_PAYPLANS_LOG_KEY_LABEL');?>
							</th>
							
							<?php if ($previous) { ?>
							<th width="43%" class="center">
								<?php echo JText::_('COM_PAYPLANS_LOG_PREVIOUS_LABEL');?>
							</th>
							<?php } ?>

							<th width="43%" class="center">
								<?php echo JText::_('COM_PAYPLANS_LOG_CURRENT_LABEL');?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($rows as $row) { ?>
						<tr class="<?php echo $row->diff ? 'info' : '';?>">
							<td>
								<?php echo $row->key;?>
							</td>

							<?php if ($previous) { ?>
							<td class="center">
								<?php echo $row->previous;?>
							</td>
							<?php } ?>

							<td class="center">
								<?php echo $row->current;?>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		<?php } else { ?>
			<?php echo $data;?>			
		<?php } ?>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-pp-default btn-sm"><?php echo JText::_('COM_PP_CLOSE_BUTTON'); ?></button>
	</buttons>
</dialog>
