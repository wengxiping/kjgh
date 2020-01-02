<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

?>
<style>
.qtc_override_sub_inst{margin-left:30px;}
</style>
<form name="adminForm" id="adminForm" class="form-validate" method="post">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invitex-admin-dashboard">
		<?php
		if(JVERSION >= '3.0'):
			 if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;

		?>
		<div id="">
			<legend><?php echo JText::_("COM_INVITEX_CHANGE_SITE_VIEW_ACCORDING_TO_TEMPLATE_BS");?></legend>
			<div class="alert alert-success">
				<?php echo JText::_("COM_INVITEX_INTRO_WE_HV_CAHGNE_DEFALT_FORNTED_VEW_IN_BS3");?>
			</div>
			<h2><?php echo JText::_("COM_INVITEX_CHANGE_SITE_VIEW_IN_BS3");?></h2>
			<div class='alert alert-warning'><b><?php echo JText::_("COM_INVITEX_IF_UR_TEMPALATE_IN_BS3");?></b></div>
			<p><?php echo JText::_("COM_INVITEX_LATEST_VERSION_FRONTEND_IN_BS3");?></p>
			<table class="table table-bordered">
				<tr><td><?php echo JText::_("COM_INVITEX_BS3_INST_1");?></td></tr>
				<tr><td><?php echo JText::_("COM_INVITEX_BS3_INST_2");?></td></tr>
				<tr><td><?php echo JText::_("COM_INVITEX_BS3_INST_3");?></td></tr>

			</table>

			<h2><?php echo JText::_("COM_INVITEX_CHANGE_SITE_VIEW_IN_BS2");?></h2>
			<div class='alert alert-warning'><b><?php echo JText::_("COM_INVITEX_IF_UR_TEMPALATE_IN_BS2");?></b></div>
			<p><?php echo JText::_("COM_INVITEX_LATEST_VERSION_FRONTEND_IN_BS2");?></p>
			<table class="table table-bordered">
				<tr><td><?php echo JText::_("COM_INVITEX_BS2_INST_1");?></td></tr>
				<tr>
					<td>
						<p><?php echo JText::_("COM_INVITEX_BS2_INST_2");?></p>
						<!-- Automation-->
						<?php
						$actionLink = JURI::base() . "index.php?option=com_invitex&task=setup";
						$overrideLink = $actionLink . "&takeBackUp=0";
						$bakupLink = $actionLink . "&takeBackUp=1";
						?>
						<p>
							<div class="alert alert-success">
								<b><i><u><?php echo JText::_("COM_INVITEX_BS2_AUTOMATION_INST_2");?></u></i></b>
							</div>
						</p>
						<span class="label label label-warning qtc_override_sub_inst">
							<?php echo JText::_("COM_INVITEX_EACH_UPGRADATION_INST");?>
						</span>
						<div class="qtc_override_sub_inst">
							<dl>
							  <dt><?php echo JText::_("COM_INVITEX_BS2_CLEAN_INSTALL");?></dt>
							  <dd>
								  <a class="btn btn-primary" href="<?php echo $overrideLink; ?>" target="_blank">
										<?php echo JText::_("COM_INVITEX_BS2_OVERRIDE_BTN");?>
									</a>
									<i class="icon-arrow-left"></i>
									<?php echo JText::_("COM_INVITEX_BS2_CLEAN_INSTALL_MSG");?>
							   </dd>
							</dl>

							<dl>
							  <dt><?php echo JText::_("COM_INVITEX_BS2_UPGRADE");?></dt>
							  <dd>
								<ol>
									<li>
										 <a class="btn btn-m btn-primary" href="<?php echo $overrideLink; ?>" target="_blank">
										<?php echo JText::_("COM_INVITEX_BS2_OVERRIDE_BTN_UPGRADE");?>
										</a>
										<i class="icon-arrow-left"></i>
									<?php echo JText::sprintf('COM_INVITEX_BS2_UPGRADE_NO_CUSTOM_CHANGE_HELP', '');?>
									<br />
									</li>
									<li>
										<a class="btn btn-medium btn-primary" href="<?php echo $bakupLink; ?>" target="_blank">
										<?php echo JText::_("COM_INVITEX_BS2_BACKUP_AND_OVERRIDE_BTN");?>
										</a>
										<i class="icon-arrow-left"></i>
										<?php echo JText::sprintf('COM_INVITEX_BS2_UPGRADE_DONE_CUSTOM_CHANGE_HELP','[LINK]');?>
									</li>

								</ol>
							  </dd>
							</dl>
						</div>

						<hr/>

						<p>
							<div class="alert alert-success">
								<b><i><u><?php echo JText::_("COM_INVITEX_BS2_MANUAL_INST_2");?></u></i></b>
							</div>
						</p>

						<span class="label label label-warning qtc_override_sub_inst">
							<?php echo JText::_("COM_INVITEX_EACH_UPGRADATION_INST");?>
						</span>
						<div class="qtc_override_sub_inst">
<!--
							<p><b><?php echo JText::_("COM_INVITEX_BS2_INSTALLATION_ND_UPGRADER_INSTRUCTION");?></b></p>
-->
							<p><b><?php echo JText::_("COM_INVITEX_BS2_SITE_COM_OVERRIDE");?></b></p>
							<ol>
							  <li><?php echo JText::_("COM_INVITEX_BS2_SITE_COM_OVERRIDE_STEP_1");?></li>
							  <li><?php echo JText::_("COM_INVITEX_BS2_SITE_COM_OVERRIDE_STEP_2");?></li>
							</ol>

						</div>
					</td>
				</tr>

			</table>

		</div
	</div> <!-- j-main-container -->
	</div>
</form>
