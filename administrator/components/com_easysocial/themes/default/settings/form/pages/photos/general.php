<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'photos.enabled', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ENABLE_PHOTOS'); ?>
				<?php echo $this->html('settings.toggle', 'photos.tagging', 'COM_ES_PHOTOS_ENABLE_TAGGING'); ?>
				<?php echo $this->html('settings.toggle', 'photos.location', 'COM_ES_PHOTOS_SETTINGS_ALLOW_LOCATION'); ?>
				<?php echo $this->html('settings.toggle', 'photos.import.exif', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_IMPORT_EXIF_DATA'); ?>
				<?php echo $this->html('settings.toggle', 'photos.original', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ALLOW_VIEW_ORIGINAL'); ?>
				<?php echo $this->html('settings.toggle', 'photos.downloads', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ALLOW_DOWNLOADS'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_PHOTO_PAGINATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'photos.pagination.photo', $this->config->get('photos.pagination.photo'), '', array('class' => 'input-short text-center')); ?>
						&nbsp;<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_PHOTO_PAGINATION_UNIT'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_ENABLE_GIF_PHOTOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'photos.gif.enabled', $this->config->get('photos.gif.enabled'),'', array('data-toggle-gif')); ?>
					</div>
					<div class="col-md-7" data-gif-message>
						<div class="o-loader o-loader--sm o-loader--inline with-text"><?php echo JText::_('COM_EASYSOCIAL_VERIFYING_API_KEY'); ?></div>
						<div role="alert" class="o-alert o-alert--success o-alert--icon o-alert--dismissible t-hidden" data-gif-success-message>
							<button type="button" class="o-alert__close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
							<strong><?php echo JText::_('COM_EASYSOCIAL_API_KEY_VERIFIED'); ?></strong>
						</div>
						<div role="alert" class="o-alert o-alert--danger o-alert--icon t-hidden" data-gif-error-message><?php echo JText::_('COM_EASYSOCIAL_API_KEY_VERIFICATION_FAILED'); ?></div>
					</div>
				</div>
				<div class="form-group" style="margin-top: 2px;">
					<div class="col-md-5"></div>
					<div class="col-md-7">
						<div class="o-alert o-alert--info">
							<?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SETTINGS_GIF_PROCESSING_DESC'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PHOTOS_SETTINGS_UPLOADER'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_PHOTOS_SETTINGS_UPLOAD_QUALITY'); ?>

					<div class="col-md-7">
						<select name="photos.uploader.quality" class="o-form-control">
							<option value="50" <?php echo $this->config->get('photos.uploader.quality') == '50' ? ' selected="selected"' : '';?>><?php echo JText::_('Low'); ?></option>
							<option value="70" <?php echo $this->config->get('photos.uploader.quality') == '70' ? ' selected="selected"' : '';?>><?php echo JText::_('Medium'); ?></option>
							<option value="90" <?php echo $this->config->get('photos.uploader.quality') == '90' ? ' selected="selected"' : '';?>><?php echo JText::_('High'); ?></option>
							<option value="100" <?php echo $this->config->get('photos.uploader.quality') == '100' ? ' selected="selected"' : '';?>><?php echo JText::_('Highest'); ?></option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
