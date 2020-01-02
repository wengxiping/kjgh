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
<div class="row-fluid">
	<table class="table">
	<thead>
		<th><small><?php echo JText::_("COM_PAYPLANS_APP_SOBIPRO_SUBMISSION_CATEGORY");?></small></th>
		<th><small><?php echo JText::_("COM_PAYPLANS_APP_SOBIPRO_SUBMISSION_ALLOWED"); ?></small></th>
		<th><small><?php echo JText::_("COM_PAYPLANS_APP_SOBIPRO_SUBMISSION_CONSUMED");?></small></th>
	</thead>
	<?php foreach ($sobipro_entries as $render) { ?>
			<tr>
				<td><small><?php  echo $render->title;?></small></td>	
				<td><small><?php  echo $render->count;?></small></td>	
				<td><small><?php  echo $render->consumed;?></small></td>	
			</tr>
	<?php } ?>
</table>
<div class="text-error" ><small><?php echo JText::_("COM_PAYPLANS_APP_SOBIPRO_SUBMISSION_NOTICE");?></small></div>
</div>
<?php 
