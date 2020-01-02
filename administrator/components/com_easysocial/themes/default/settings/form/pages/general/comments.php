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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_COMMENTS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'stream.comments.guestview', 'COM_EASYSOCIAL_STREAM_SETTINGS_ALLOW_GUEST_VIEW_COMMENTS'); ?>
				<?php echo $this->html('settings.toggle', 'comments.smileys', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_ALLOW_SMILEYS'); ?>
				<?php echo $this->html('settings.textbox', 'comments.limit', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_PAGINATION_LIMIT', '', array('postfix' => 'COM_EASYSOCIAL_COMMENTS_SETTINGS_PAGINATION_LIMIT_UNIT', 'size' => 7), '', 'input-short text-center'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_ATTACHMENTS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'comments.attachments.enabled', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_ENABLE_ATTACHMENTS'); ?>
				<?php echo $this->html('settings.toggle', 'comments.resize.enabled', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_RESIZE_IMAGES'); ?>
				<?php echo $this->html('settings.textbox', 'comments.resize.width', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_RESIZE_IMAGES_MAX_WIDTH', '', array('postfix' => 'COM_EASYSOCIAL_PIXELS'), '', 'input-short text-center'); ?>
				<?php echo $this->html('settings.textbox', 'comments.resize.height', 'COM_EASYSOCIAL_COMMENTS_SETTINGS_RESIZE_IMAGES_MAX_HEIGHT', '', array('postfix' => 'COM_EASYSOCIAL_PIXELS'), '', 'input-short text-center'); ?>
				<?php echo $this->html('settings.textbox', 'comments.attachments.maxsize', 'COM_ES_COMMENTS_SETTINGS_ATTACHMENT_MAX_SIZE', '', array('postfix' => 'COM_ES_UNIT_MB', 'size' => 7), '', 'text-center'); ?>
			</div>
		</div>
	</div>
</div>
