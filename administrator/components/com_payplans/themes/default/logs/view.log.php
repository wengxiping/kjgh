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
<div class="pp-logs-view">
	<form action="<?php //echo $uri; ?>" method="post" name="adminForm">
		<div class="row-fluid pp-gap-top10">
		<?php if(is_array($data)==false):?>
				<?php echo $data;?>
		<?php else:?>
			<div>
			<?php $value  = !empty($data['current']) ? $data['current']	: array();?>
				<table class="table">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_PAYPLANS_LOG_KEY_LABEL');?></th>
							<th><?php echo JText::_('COM_PAYPLANS_LOG_VALUE_LABEL');?></th>
						</tr>
					</thead>
					<tbody>
						<?php $base_record	= is_array($value) ? $value : array($value); ?>
						<?php foreach($base_record as $key => $val):?>
						<tr>
							<td><?php echo $key;?></td>
							<td>
								<?php if(is_array($val) && !empty($val)):?>
								<?php	// Check numeric keys exist in array(ie. sequential array)
										if(range(0, count($val)-1) === array_keys($val)){
											print implode('<br/>', $val);
										}else {
											echo http_build_query($val, '', '<br/>');
										} ?>
							
								<?php elseif($key === 'status'):?>
									<?php echo (isset($val) && "" !== JString::trim($val)) ? JText::_('COM_PAYPLANS_STATUS_'.PayplansStatus::getName($val)):"&nbsp;";?>
								<?php else:?>
									<?php echo $val; ?>
								<?php endif;?>
							</td>
						</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			
			</div>
		<?php endif;?>

		<input type="hidden" name="task" value="close" />
		</div>
	</form>
</div>
<?php

