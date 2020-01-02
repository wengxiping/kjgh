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
defined('_JEXEC') or die('Unauthorized Access');
	
$layout = JFactory::getApplication()->input->get('layout', '', 'default');
$view = JFactory::getApplication()->input->get('view', '', 'default');

$active = 'is-active';
if ($layout == 'preferences' || $layout == 'download') {
	$active = '';
} 

$activePlan = '';
if ($view == 'plan' || $view == '') {
	$activePlan = 'is-active';
	$active = '';
}
?>
<div class="pp-toolbar t-lg-mb--lg">

	<div class="pp-toolbar__item pp-toolbar__item--home-submenu" data-pp-toolbar-menu>
		<div class="o-nav pp-toolbar__o-nav">
			
			<?php if ($this->my->id) { ?>
			<div class="o-nav__item  <?php echo $active; ?>">
				<a href="<?php echo PPR::_('index.php?option=com_payplans&view=dashboard');?>" class="o-nav__link pp-toolbar__link">
					<span><?php echo JText::_('COM_PP_PURCHASES');?></span>
				</a>
			</div>
			<?php } ?>

			<div class="o-nav__item <?php echo $activePlan; ?>">
				<a href="<?php echo PPR::_('index.php?option=com_payplans&view=plan&from=dashboard');?>" class="o-nav__link pp-toolbar__link">
					<span><?php echo JText::_('COM_PP_PLANS');?></span>
				</a>
			</div>
		</div>
	</div>

	<?php if ($this->my->id) { ?>
		<div class="pp-toolbar__item pp-toolbar__item--action">
			<nav class="o-nav pp-toolbar__o-nav">
				<div class="o-nav__item">
					<a href="javascript:void(0);" class="o-nav__link pp-toolbar__link dropdown-toggle_" data-pp-toggle="dropdown">
						<i class="fa fa-cog"></i>
					</a>
					<div class="pp-toolbar__dropdown-menu pp-toolbar__dropdown-menu--signin o-dropdown-menu">
						
						<div class="o-dropdown-menu-content">
							
							<div class="o-dropdown-menu-content__nav">
								<div class="o-dropdown-menu-nav">

									<div class="o-dropdown-menu-nav__item ">
										<span class="o-dropdown-menu-nav__link">

										<div class="o-dropdown-menu-nav__name"><?php echo JText::_('COM_PP_ACCOUNT');?></div>
											<ol class="g-list-unstyled o-dropdown-menu-nav__meta-lists">
												<?php if ($this->config->get('user_edit_preferences') || $this->config->get('user_edit_customdetails')) { ?>
												<li>
													<a href="<?php echo PPR::_('index.php?option=com_payplans&view=dashboard&layout=preferences');?>"><?php echo JText::_('COM_PP_EDIT_PREFERENCES');?></a>
												</li>
												<?php } ?>

												<?php if ($this->config->get('users_download')) { ?>
												<li>
													<a href="<?php echo PPR::_('index.php?option=com_payplans&view=dashboard&layout=download');?>"><?php echo JText::_('COM_PP_DOWNLOAD_MY_DATA');?></a>
												</li>
												<?php } ?>

												<li>
													<a href="javascript:void(0);" data-pp-logout><?php echo JText::_('COM_PP_SIGN_OUT');?></a>
												</li>
												<form action="<?php echo JRoute::_('index.php');?>" method="post" data-pp-logout-form>
													<?php echo $this->html('form.hidden', 'option', 'com_users'); ?>
													<?php echo $this->html('form.hidden', 'task', 'user.logout'); ?>
													<?php echo $this->html('form.token'); ?>
												</form>
											</ol>
										</span>
									</div>
								</div>

							</div>
							
						</div>
					</div>
				</div>

			</nav>
		</div>
	<?php } ?>
</div>