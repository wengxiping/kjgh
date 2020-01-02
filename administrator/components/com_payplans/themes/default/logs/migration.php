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

$input = PayplansFactory::getApplication()->input;
$action = $input->get('action', false); 

if('inProcess' == $action || 'start' == $action) { 
?>
	<div class="row-fluid text-center pp-gap-top10">
		<div class="span12 text-error"><?php echo JText::_('COM_PAYPLANS_LOG_MIGRATION_MSG_DONOT_CLOSE');?></div>
		<div class="progress progress-striped active span11">
			<div class="bar" style="width: <?php echo $progress;?>%;"></div>
		</div>
		<div class="span11 loading"></div>
		<div class="span11">
		   <h5> <span id="pp-rebuild-progress-count">
				<?php echo JText::sprintf('COM_PAYPLANS_LOG_MIGRATION_INPROCESS',$exeCount,$migrate_total);?>
			</span></h5>
		</div>
	</div>
<?php 
	//When action is complete then creates contents for complete dialog box.
}
elseif('complete' == $action){?>
		<div class="text-center"><b><?php echo JText::_('COM_PAYPLANS_LOG_MIGRATION_COMPLETED');?></b></div>
<?php 		
	}
?>
