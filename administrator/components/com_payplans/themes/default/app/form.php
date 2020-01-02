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
if(defined('_JEXEC')===false) die();?>
<div id="pp-app-edit">
<form action="<?php echo $uri; ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend> <?php echo JText::_($appData['name'])." - ".JText::_('COM_PAYPLANS_APP_EDIT_APP_DETAILS' ); ?> </legend>
		
				<div class="control-group">
										<div class="control-label"><?php echo $form->getLabel('title'); ?> </div>
										<div class="controls"><?php echo $form->getInput('title'); ?></div>	
					</div>	
					
				<div class="control-group">
										<div class="control-label"><?php echo $form->getLabel('published'); ?> </div>
										<div class="controls"><?php echo $form->getInput('published'); ?></div>	
					</div>	
					
				<?php foreach ($form->getFieldset('core_params') as $field):?>
					<?php $class = $field->group.$field->fieldname; ?>
					<div class="control-group <?php echo $class;?>">
						<div class="control-label"><?php echo $field->label; ?> </div>
						<div class="controls"><?php echo $field->input; ?></div>								
					</div>
				<?php endforeach;?>
				 
				<div class="control-group core_paramsappplans">
					<div class="control-label" title="<?php echo JText::_('COM_PAYPLANS_APP_EDIT_APPS_PLAN_TITLE'); ?>::<?php echo JText::_('COM_PAYPLANS_APP_EDIT_APPS_PLAN_TITLE_DESC'); ?>" >
						<?php echo JText::_( 'COM_PAYPLANS_APP_EDIT_APPS_PLAN_TITLE' ); ?>
					</div>
					<div class="controls pp-word-wrap">
						<?php $plans = $app->getPlans();
						echo PayplansHtml::_('plans.edit', 'Payplans_form[appplans]', $plans, array('multiple'=>true,'usexifbselect' => true, 'style' => 'class="required"'));?>
					</div>
				</div>
				
				<div class="control-group">
										<div class="control-label"><?php echo $form->getLabel('description'); ?> </div>
										<div class="controls"><?php echo $form->getInput('description'); ?></div>	
				</div>
					
						
			</fieldset>
	
			<!-- Logs -->
			<?php echo $this->loadTemplate('edit_log');?>
		</div>
		
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend>
					<?php echo JText::_( 'COM_PAYPLANS_APP_EDIT_APP_PARAMETERS' ); ?>
				</legend>
					
				<?php foreach ($form->getFieldset('app_params') as $field):?>
					<?php $class = $field->group.$field->fieldname; ?>
					<div class="control-group <?php echo $class;?>">
						<div class="control-label"><?php echo $field->label; ?> </div>
						<div class="controls"><?php echo $field->input; ?></div>								
					</div>
				<?php endforeach;?>
		
			</fieldset>
			<?php if(isset($appData['help'])):?>
			<?php $help = preg_replace('/\s+/', '', $appData['help']);?>
			<?php if(!empty($help)):?>
				<fieldset class="adminform">
					<legend onClick="xi.jQuery('.pp-app-help').slideToggle();" class="pp-cursor-pointer">
						<span class="show pp-app-help">[+]</span>
						 <?php echo JText::_( 'COM_PAYPLANS_APP_EDIT_APP_HELP' ); ?>
					</legend>
					<div class="hide pp-app-help"><?php echo JText::_($appData['help']); ?></div>
				</fieldset>
			<?php endif;?>
			<?php endif;?>
		</div>
		
		<?php echo $form->getInput('app_id'); ?>
		<?php echo $form->getInput('type'); ?>
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="boxchecked" value="1" />
		</div>
	</form>
	
	<ul id="myTabTabs" class="nav nav-tabs hide">
	<li class="active"><a data-toggle="tab"href="#ppinstall" id="installedapps" >Available</a></li>
	<li class=""><a data-toggle="tab" id="manageapps" href="#ppmanage">App instance</a></li>
	<li class=""><a data-toggle="tab"href="#ppavailable" target="_blank" id="availableapps">App store</a></li></ul>
	
</div>
<?php 
