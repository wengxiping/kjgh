<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="invitex_title_inv">
	<?php
		$title = JText::_('LOGIN_TITLE');

		if (JFactory::getApplication()->input->get('invite_anywhere') == '1')
		{
			$type_data = $this->typedata;
			$title = $type_data->name;
		}

		if ($layout == 'default' || $layout == 'send_invites' || $layout == 'black_white' || $layout == 'menu' || JFactory::getApplication()->input->get('invite_anywhere') == '1')
		{
				?>
			<h2><?php echo $title?></h2>
			<?php
		}

		if (((!empty($_COOKIE['invitex_reg_user'])) || (!empty($_COOKIE['invitex_after_login']))) &&(JFactory::getApplication()->input->get('invite_anywhere') != '1'))
		{
			$user = JFactory::getUser();
		?>
	<div class="invitex_skip">
		<button class="btn btn-primary" onclick='window.location="<?php echo JRoute::_('index.php?option=com_invitex&task=skip',false);?>"'><?php echo JText::_('INV_SKIP');?></button>
	</div>
	<br><br>
	<?php
		}
		?>
</div>
<!-- end of invitex_title div -->
<!-- 设计图没有先注释 -->
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>" style="display:none;">
	<?php
	if (JFactory::getApplication()->input->get('rout') != 'preview')
	{
		if ($this->invitex_params->get('show_menu'))
		{
			if($this->invitex_params->get('inv_look') == 1)
			{
			?>
				<div class="uiStepList pbs">
					<ol id="invitex_ol">
						<li class="uiStep uiStepFirst uiStepSelected" id="firstli">
							<div class="part back"></div>
							<div class="part middle">
								<div class="inv_step_content">
									<div class="invitex_step_heading">
										<?php
											if (JFactory::getApplication()->input->get('layout') == 'send_invites')
											{
											?>
										<i class="glyphicon glyphicon-home inv_pointer"  onclick='window.location="<?php echo $this->invite_url?>"' title="<?php echo JText::_('BACK_TO_INVITEX');?>"></i>
										<?php
											}?>
										<?php echo JText::_('IX_STEP1');?>
									</div>
									<span class="description"><?php echo JText::_('IMPORT_CON');?></span>
								</div>
							</div>
							<div class="part point"></div>
						</li>
						<li class="uiStep" id="secondli">
							<div class="part back"></div>
							<div class="part middle">
								<div class="inv_step_content">
									<div class="invitex_step_heading"><?php echo JText::_('IX_STEP2');?></div>
									<span class="description"><?php echo JText::_('INV_FRIEND');?></span>
								</div>
							</div>
							<div class="part point"></div>
						</li>
						<li class="uiStep uiStepLast" id="thirdli" onclick="">
						<div class="part back"></div>
						<div class="part middle">
							<div class="inv_step_content">
								<div class="invitex_step_heading"><?php echo JText::_('IX_STEP3');?></div>
								<span class="description"><?php echo JText::_('ADD_FRIEND');?></span>
							</div>
						</div>
						<div class="part point"></div>
						</li>
					</ol>
				</div>
			<?php
			}
			else
			{
			?>
				<div class=" clearfix">
				<div class="inv_steps_parent">
					<!--MyWizard STARTS-->
					<div id="MyWizard" class="wizard">
						<ol class="inv-steps-ol clearfix" role="tab">
							<li aria-selected="true" class="current proceed active col-xs-12 col-md-4" id="proceed1" >
								<span class="badge">
								<?php
									if (JFactory::getApplication()->input->get('layout') == 'send_invites')
									{
									?>
								<i class="glyphicon glyphicon-home inv_pointer"  onclick='window.location="<?php echo $this->invite_url?>"' title="<?php echo JText::_('BACK_TO_INVITEX');?>"></i>
								<?php
									}
									?>
								<?php echo JText::_('COM_INVITEX_STEP1');?>
								</span>
								<span class=""><?php echo JText::_('IMPORT_CON');?></span>
								<span class="chevron"></span>
							</li>
							<li aria-selected="false"   id="proceed2" class="proceed col-xs-12 col-md-4">
								<span class="badge"><?php echo JText::_('COM_INVITEX_STEP2');?></span>
								<span class=""><?php echo JText::_('INV_FRIEND');?></span>
								<span class="chevron"></span>
								<span class="chevron"></span>
							</li>
							<li aria-selected="false" id="proceed3" class="proceed col-xs-12 col-md-3">
								<span class="badge"><?php echo JText::_('COM_INVITEX_STEP3');?></span>
								<span class=""><?php echo JText::_('ADD_FRIEND');?></span>
								<span class="chevron"></span>
							</li>
						</ol>
						<div class="clearfix"></div>
					</div>
					<!--MyWizard END-->
				</div>
				</div>
			<?php
			}
		}
	}
	?>
	</div>
