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

<form action="<?php echo $uri; ?>" method="post" name="adminForm" id="adminForm">
<fieldset class="form-horizontal">
<ul id="myTabTabs" class="nav nav-tabs">
		<li ><a data-toggle="tab"href="#ppinstall" id="installedapps" ><img src=<?php echo JURI::root().'administrator/components/com_payplans/templates/default/_media/images/icons/my-pp-apps-icon.png'; ?> />&nbsp;&nbsp;<?php echo JText::_('COM_PAYPLANS_APP_AVAILABLE_APPS'); ?> </a></li>
		<li class="active"><a data-toggle="tab" id="manageapps" href="#ppmanage"><img src=<?php echo JURI::root().'administrator/components/com_payplans/templates/default/_media/images/icons/pp-apps-instance-icon.png'; ?> />&nbsp;&nbsp;<?php echo JText::_('COM_PAYPLANS_APP_APPS_INSTANCE'); ?> </a></li>
		<li class=""><a data-toggle="tab" id="availableapps"  href="#ppavailable" ><img src=<?php echo JURI::root().'administrator/components/com_payplans/templates/default/_media/images/icons/pp-app-store-icon.png'; ?> />  <?php echo JText::_('COM_PAYPLANS_APP_APP_STORE'); ?> </a></li></ul>
	
	<div id="myTabContent" class="tab-content">

<div class="tab-pane" id="ppinstall">

			<?php echo $this->loadTemplate('selectapp');?>
			</div>
			  <div class="tab-pane active"  id="ppmanage">
				  <?php echo $this->loadTemplate('filter'); ?>
				<div class='hero-unit'>
					<h1><?php echo $heading; ?></h1>
					<p><?php echo $msg; ?></p>
				</div>
			</div>
			<div class="tab-pane" id="ppavailable">
				<?php echo JText::_("COM_PAYPLANS_APP_REDIRECT_TO_APP_STORE");?>
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="active_tab" value="manageapps" />
	<input type="hidden" name="active_tab_content" value="ppmanage" />
	<input type="hidden" name="boxchecked" value="0" />
</form>