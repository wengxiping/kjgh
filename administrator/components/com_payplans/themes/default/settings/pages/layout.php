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
<div class="row">
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PAYPLANS_CONFIG_CUSTOMIZATION_INVOICE'); ?>
			
			<div class="panel-body">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'companyAddress'); ?>
					
					<div class="o-control-input">
						<?php echo $form->getInput('companyAddress'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('companyName'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('companyName'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('companyCityCountry'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('companyCityCountry'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('companyPhone'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('companyPhone'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('add_token'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('add_token'); ?><br/>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
							<?php echo $form->getInput('rewriter'); ?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('companyLogo'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('companyLogo'); ?>
						<?php $logoValue = $form->getValue('companyLogo');?>
						<?php $subparam = '';?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<?php if(!empty($logoValue)):?>
							<?php 	
									ob_start();
									?>
										<a onclick="xi.jQuery.apprise('Are you sure to delete?', 
																		{'verify':true}, 
																		function(r){
																			if(r){
																				payplans.url.redirect('<?php echo XiRoute::_('index.php?option=com_payplans&view=config&task=removecompanylogo'); ?>');
																			} 
																			else{
																				return false;
																			}
																		}
																		);" href="#">
																				
											Delete
										</a>
										<p><img style="max-width: 250px;" src="<?php echo PayplansHelperTemplate::mediaURI(XiHelperJoomla::getRootPath().DS.$logoValue, false) ?>" /></p>
									<?php 
									$subparam = ob_get_contents();
									ob_end_clean();
						endif;?>
						<?php echo $subparam;?>
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('show_blank_token'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('show_blank_token'); ?>
					</div>
				</div>
				
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('note'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('note'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PAYPLANS_CONFIG_CUSTOMIZATION_DASHBOARD'); ?>

			<div class="panel-body">
				<?php foreach ($form->getFieldset('dashboard') as $field) { ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
		
	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PAYPLANS_CONFIG_CUSTOMIZATION_TEMPLATE'); ?>

			<div class="panel-body">
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('expert_use_jquery'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('expert_use_jquery'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('expert_use_bootstrap_jquery'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('expert_use_bootstrap_jquery'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('expert_use_bootstrap_css'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('expert_use_bootstrap_css'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('expert_useminjs'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('expert_useminjs'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('expert_use_font_awesome'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('expert_use_font_awesome'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('rtl_support'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('rtl_support'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('layout'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('layout'); ?>
					</div>
				</div>

				<div class="control-group layoutrow_plan_counter">
					<div class="control-label">
						<?php echo $form->getLabel('row_plan_counter'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('row_plan_counter'); ?>
					</div>
				</div>

				<div class="control-group">
					<div class="control-label">
						<?php echo $form->getLabel('use_template'); ?>
					</div>
					<div class="controls">
						<?php echo $form->getInput('use_template'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>