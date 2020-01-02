<?php
/**
* @package		PayPlans
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_GENERAL_FEATURES'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'user_edit_preferences', 'COM_PP_ALLOW_USER_EDIT_PREFERENCES'); ?>
				<?php echo $this->html('settings.toggle', 'user_edit_customdetails', 'COM_PP_ALLOW_USER_EDIT_CUSTOMDETAILS'); ?>
				<?php echo $this->html('settings.toggle', 'users_download', 'COM_PP_ALLOW_USER_DOWNLOAD_DATA'); ?>
				<?php echo $this->html('settings.toggle', 'user_delete_orders', 'COM_PP_ALLOW_USER_DELETE_INCOMPLETE_ORDER'); ?>

				<?php echo $this->html('settings.textbox', 'users_download_expiry', 'COM_PP_USER_DOWNLOAD_EXPIRY', '', array('postfix' => 'Days', 'size' => 8), '', 't-text--center'); ?>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_PP_CONFIG_USER_LOGIN_REDIRECTION'); ?>
	
			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'users_login_redirection', 'COM_PP_ALLOW_LOGIN_REDIRECTION'); ?>
				<?php echo $this->html('settings.textbox', 'users_subscribers_redirect', 'COM_PP_SUBSCRIBERS_LOGIN_REDIRECTION_URL'); ?>
				<?php echo $this->html('settings.textbox', 'users_nonsubscribers_redirect', 'COM_PP_NONSUBSCRIBERS_LOGIN_REDIRECTION_URL'); ?>
			</div>
		</div>
	</div>
</div>
