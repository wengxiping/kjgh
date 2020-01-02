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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_NOTIFICATIONS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.email.enabled', 'COM_ES_ENABLE_EMAIL_NOTIFICATIONS'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.system.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_SYSTEM_NOTIFICATION'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.system.autoread', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_SYSTEM_AUTOMATICALLY_MARK_AS_READ'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.friends.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_FRIEND_NOTIFICATION'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.conversation.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_CONVERSATION_NOTIFICATION'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.conversation.autoread', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CONVERSATIONS_AUTOMATICALLY_MARK_AS_READ'); ?>
				<?php echo $this->html('settings.textbox', 'notifications.polling.interval', 'COM_ES_NOTIFICATIONS_SETTINGS_POLLING_INTERVAL', '', array('postfix' => 'COM_EASYSOCIAL_SECONDS', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_BROADCAST_SETTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.broadcast.popup', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_ENABLE_BROADCAST_POPUP'); ?>
				<?php echo $this->html('settings.toggle', 'notifications.broadcast.sticky', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_STICKY_POPUP'); ?>
				<?php echo $this->html('settings.textbox', 'notifications.broadcast.period', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_BROADCAST_PERIOD', '', array('postfix' => 'COM_EASYSOCIAL_SECONDS', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'notifications.cleanup.enabled', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_ENABLE'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'notifications.cleanup.duration', $this->config->get('notifications.cleanup.duration'), array(
								array('value' => '3', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_3_MONTHS'),
								array('value' => '6', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_6_MONTHS'),
								array('value' => '12', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_12_MONTHS'),
								array('value' => '18', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_18_MONTHS'),
								array('value' => '24', 'text' => 'COM_EASYSOCIAL_NOTIFICATIONS_SETTINGS_CLEANUP_24_MONTHS')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
