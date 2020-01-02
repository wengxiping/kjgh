<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ENABLE_AUDIOS'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.enabled', $this->config->get('audio.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ALLOW_AUDIO_UPLOADS'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.uploads', $this->config->get('audio.uploads'), 'audio-uploads', array('data-audio-uploads')); ?>
					</div>
				</div>

				<div class="form-group <?php echo !$this->config->get('audio.uploads') ? 't-hidden' : '';?>"" data-encoder-option>
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ENABLE_ENCODE_AUDIO'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.allowencode', $this->config->get('audio.allowencode'), 'enable-encoder', array('data-enable-encoder')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ALLOW_AUDIO_EMBEDS'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.embeds', $this->config->get('audio.embeds'), 'audio-embed', array('data-audio-embed')); ?>
					</div>
				</div>

				<div class="form-group" data-embed-spotify>
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ALLOW_EMBEDS_SPOTIFY'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.embed.spotify', $this->config->get('audio.embed.spotify'), 'audio-spotify'); ?>
					</div>
				</div>

				<div class="form-group" data-embed-soundcloud>
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ALLOW_EMBEDS_SOUNDCLOUD'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.embed.soundcloud', $this->config->get('audio.embed.soundcloud'), 'audio-soundcloud'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ALLOW_DOWNLOADS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.downloads', $this->config->get('audio.downloads')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel <?php echo !$this->config->get('audio.allowencode') ? 't-hidden' : '';?>" data-audio-encoding>
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_ENCODING'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ENCODER_PATH'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'audio.encoder', $this->config->get('audio.encoder')); ?>

						<?php if ($this->config->get('audio.encoder') && !JFile::exists($this->config->get('audio.encoder'))) { ?>
							<div class="t-text--danger">
								<b><?php echo JText::_('COM_ES_AUDIO_SETTINGS_ENCODER_PATH_NOT_FOUND');?></b>
							</div>
						<?php } ?>
						<div class="help-block">
							<?php echo JText::_('COM_ES_AUDIO_SETTINGS_ENCODER_COMPATIBLE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DELETE_PROCESSED_AUDIOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.delete', $this->config->get('audio.delete')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_ENCODE_AUDIO_AFTER_UPLOAD'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.autoencode', $this->config->get('audio.autoencode')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_AUTOMATICALLY_IMPORT_METADATA'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.autoimportdata', $this->config->get('audio.autoimportdata')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_MAXIMUM_BITRATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'audio.bitrate', $this->config->get('audio.bitrate'), array(
								array('value' => '32k', 'text' => '32 kbps'),
								array('value' => '64k', 'text' => '64 kbps'),
								array('value' => '96k', 'text' => '96 kbps'),
								array('value' => '192k', 'text' => '192 kbps'),
								array('value' => '224k', 'text' => '224 kbps')
						)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
