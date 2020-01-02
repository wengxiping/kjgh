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
<?php if ($transaction_html) { ?>
<?php 
	$data = array('param6', 'param11', 'param4', 'param9',
							'param23', 'param3','x_response_code', 'x_response_reason_code', 
							'x_response_reason_text', 'param51', 'param13', 
							'param14', 'param15','param16', 'param17', 'param18',
							'param19', 'param20', 'param21');
?>
<table class="app-table table table-hover">
	<thead>
		<tr>
			<td width="20%">
				<?php echo JText::_('Key'); ?>
			</td>
			<td>
				<?php echo JText::_('Value'); ?>
			</td>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($data as $key) { ?>
			<?php if (isset($transaction_html[$key])) { ?>
			<tr>
				<td>
					<?php echo $key;?>
				</td>
				<td>
					<?php echo $transaction_html[$key] ? $transaction_html[$key] : '&mdash;'; ?>
				</td>
			</tr>
			<?php } ?>
		<?php } ?>
	</tbody>
</table>
<?php } ?>
		  

