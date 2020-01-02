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
	<div class="span8">
		<?php echo $data;?>
	</div>

<?php else:?>
	<div>
	<?php $diff = false;?>
	<?php $previous = $data['previous'];?>
	<?php $current  = $data['current'];?>
		
	<div class="row-fluid">
	
		<table class="table">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_PAYPLANS_LOG_KEY_LABEL');?></th>
					<th><?php echo JText::_('COM_PAYPLANS_LOG_PREVIOUS_LABEL');?></th>
					<th><?php echo JText::_('COM_PAYPLANS_LOG_CURRENT_LABEL');?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$pre_exist = !empty($previous);
					$cur_exist = !empty($current);
			
					$base_record 	= $pre_exist ? $previous : $current;
					$base_record	= is_array($base_record) ? $base_record : array($base_record);		
				?>
				<?php foreach($base_record as $key => $val):?>
				<?php $pre_value = $pre_exist ? (isset($previous[$key]))?$previous[$key]:'' : ''; ?>
				<?php $cur_value = $cur_exist ? (isset($current[$key]))?$current[$key]:''	: '';?>
				<?php $diff = ($cur_value != $pre_value); ?>
				
					<tr class="<?php echo $diff ? " info":""; ?>">
						<td><?php echo $key;?></td>
						<td>
							<?php if(is_array($pre_value) && !empty($pre_value)):?>
							<?php	// Check numeric keys exist in array(ie. sequential array)
									if(range(0, count($pre_value)-1) === array_keys($pre_value)){
										print implode('<br/>', $pre_value);
									}else {
										echo http_build_query($pre_value, '', '<br/>');
									} ?>
					
							<?php elseif($key === 'status'):?>
								<?php echo (isset($pre_value) && "" !== JString::trim($pre_value)) ? JText::_('COM_PAYPLANS_STATUS_'.PayplansStatus::getName($pre_value)):"&nbsp;";?>
								<?php elseif(empty($pre_value)):?>
									<?php echo "&nbsp;" ;?>
							<?php else:?>
								<?php echo $pre_value; ?>
							<?php endif;?>
						</td>
						<td>
							<?php if(is_array($cur_value) && !empty($cur_value)):?>
							<?php 	// Check numeric keys exist in array(ie. sequential array)
									if(range(0, count($cur_value)-1) === array_keys($cur_value)){
										print implode('<br/>', $cur_value);
									}else {
										echo http_build_query($cur_value, '', '<br/>');
									} ?>
					
							<?php
							// if key is 0 then 0 == 'status' will return true because of type casting. So it is required to check the type also.
							elseif($key ==='status'):?>
								<?php echo (isset($cur_value) && "" !== JString::trim($cur_value)) ? JText::_('COM_PAYPLANS_STATUS_'.PayplansStatus::getName($cur_value)):"&nbsp;";?>
							<?php elseif($cur_value !== 0 && empty($cur_value)):?>
									<?php echo "&nbsp;" ;?>
							<?php else:?>
								<?php echo $cur_value; ?>
							<?php endif;?>
						</td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		</div>
	</div>
			
	<?php endif;?>

<input type="hidden" name="task" value="close" />
</div>
</form>
</div>
<?php 