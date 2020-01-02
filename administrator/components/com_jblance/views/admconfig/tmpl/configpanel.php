<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	14 March 2012
 * @file name	:	views/admconfig/tmpl/configpanel.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Shows list of Users (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 
 $link_dashboard 	= JRoute::_('index.php?option=com_jblance');
 $link_compsetting	= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=config');
 $link_usergroup	= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showusergroup');
 $link_plan			= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showplan');
 $link_paymode		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showpaymode');
 $link_customfield	= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showcustomfield');
 $link_emailtemp	= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=emailtemplate&tempfor=subscr-pending');
 $link_category		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showcategory');
 $link_budget		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showbudget');
 $link_duration		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showduration');
 $link_location		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=showlocation');
 $link_optimise		= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=optimise');
 $link_filemanager	= JRoute::_('index.php?option=com_jblance&view=admconfig&layout=filemanager');
?>
<div id="j-sidebar-container" class="span2">
	<?php include_once(JPATH_COMPONENT.'/views/configmenu.php'); ?>
</div>
<div id="j-main-container" class="span10">
	<div class="alert alert-info">
		<h4><?php echo JText::_('COM_JBLANCE_CONFIG');?></h4>
		<p><?php echo JText::_('COM_JBLANCE_CONFIG_DESC');?></p>
	</div>
	<div class="well well-small white">
		<div id="dashboard-icons" class="btn-group">
			<a class="btn" href="<?php echo $link_dashboard; ?>">
				<i class="icon-big icon-dashboard"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_JOOMBRI_DASHBOARD') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_compsetting; ?>">
				<i class="icon-big icon-tools"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_COMPONENT_SETTINGS') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_usergroup; ?>">
				<i class="icon-big icon-users"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_USER_GROUPS') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_plan; ?>">
				<i class="icon-big icon-shield"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_SUBSCRIPTION_PLANS') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_paymode; ?>">
				<i class="icon-big icon-cart"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_PAYMENT_GATEWAYS') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_customfield; ?>">
				<i class="icon-big icon-list-2"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_CUSTOM_FIELDS') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_emailtemp; ?>">
				<i class="icon-big icon-mail-2"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_EMAIL_TEMPLATES') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_category; ?>">
				<i class="icon-big icon-tree"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_CATEGORIES') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_budget; ?>">
				<i class="icon-big icon-bars"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_BUDGET_RANGE') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_duration; ?>">
				<i class="icon-big icon-calendar"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_TOOLBAR_PROJECT_DURATION') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_location; ?>">
				<i class="icon-big icon-location"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_LOCATION') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_optimise; ?>">
				<i class="icon-big icon-database"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_OPTIMISE_DATABASE') ?></span>
			</a>
			<a class="btn" href="<?php echo $link_filemanager; ?>">
				<i class="icon-big icon-folder-open"></i><br/>
				<span class="title"><?php echo JText::_('COM_JBLANCE_TOOLBAR_CONFIGURATION_FILE_MANAGER') ?></span>
			</a>
		</div>
	</div>
</div>
