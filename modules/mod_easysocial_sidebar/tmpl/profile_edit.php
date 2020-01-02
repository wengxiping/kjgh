<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-sidebar <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar" data-sidebar>
		<?php echo $this->lib->render('module' , 'es-profile-edit-sidebar-top' , 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($this->lib->config->get('users.layout.profiletitle', true) && $this->lib->my->hasCommunityAccess()) { ?>
			<div class="es-side-widget">
				<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_YOUR_PROFILE'); ?>

				<div class="es-side-widget__bd">
					<div class="es-side-profile-info">
						<?php echo JText::sprintf('COM_EASYSOCIAL_PROFILE_SIDEBAR_YOUR_PROFILE_INFO', '<a href="' . $profile->getPermalink() . '">' . $profile->getTitle() . '</a>');?>
					</div>

					<?php if ($profilesCount > 1 && $this->lib->my->canSwitchProfile()) { ?>
					<a href="<?php echo ESR::profile(array('layout' => 'switchProfile'));?>" class="btn btn-es-default-o btn-sm btn-block t-lg-mt--md">
						<?php echo JText::_('COM_EASYSOCIAL_PROFILE_SIDEBAR_SWITCH_PROFILE');?>
					</a>
					<?php } ?>
				</div>
			</div>
			<hr class="es-hr" />
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_ABOUT'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php $i = 0; ?>
					<?php foreach ($allSteps as $step) { ?>
						<li class="o-tabs__item<?php echo ($i == 0 && !$activeStep) || ($activeStep && $activeStep == $step->id) ? ' active' :'';?>" data-profile-edit-fields-step data-for="<?php echo $step->id;?>" data-actions="1">
							<a class="o-tabs__link" href="javascript:void(0);"><?php echo $step->get('title'); ?></a>
						</li>
						<?php $i++; ?>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($oauthClients) { ?>
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_SOCIALIZE'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php foreach ($oauthClients as $client) { ?>
					<li class="o-tabs__item" data-profile-edit-fields-step data-for="oauth-<?php echo $client->getType();?>" data-actions="0">
						<a class="o-tabs__link" href="javascript:void(0);"><?php echo $client->getTitle();?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>

		<hr class="es-hr" />
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_OTHER_LINKS');?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<?php if ($this->lib->config->get('privacy.enabled') && $this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'editPrivacy'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_MANAGE_PRIVACY');?></a>
					</li>
					<?php } ?>

					<?php if ($this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'editNotifications'));?>" class="o-tabs__link"><?php echo JText::_('COM_EASYSOCIAL_MANAGE_ALERTS');?></a>
					</li>
					<?php } ?>

					<?php if ($showVerificationLink && $this->lib->my->hasCommunityAccess()) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'submitVerification'));?>" class="o-tabs__link"><?php echo JText::_('COM_ES_SUBMIT_VERIFICATION');?></a>
					</li>
					<?php } ?>
					<?php if ($this->lib->config->get('users.download.enabled')) { ?>
					<li class="o-tabs__item">
						<a href="<?php echo ESR::profile(array('layout' => 'download')); ?>" class="o-tabs__link"><?php echo JText::_('COM_ES_GDPR_DOWNLOAD_YOUR_INFORMATION'); ?></a>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php if ($this->lib->my->deleteable()) { ?>
		<hr class="es-hr" />
		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_PROFILE_SIDEBAR_DELETE'); ?>

			<div class="es-side-widget__bd">
				<a href="javascript:void(0);" class="t-text--danger" data-profile-edit-delete>
					<?php echo JText::_('COM_EASYSOCIAL_DELETE_YOUR_PROFILE_BUTTON');?>
				</a>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->lib->render('module' , 'es-profile-edit-sidebar-bottom' , 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>

<script type="text/javascript">
EasySocial
.require()
.script('site/profile/edit')
.done(function($){
	$('body').implement(EasySocial.Controller.Profile.Edit, {
		userid: <?php echo $this->lib->my->id; ?>,
		saveLogic: "<?php echo $this->lib->config->get('users.profile.editLogic', 'default'); ?>"
	});
});
</script>
