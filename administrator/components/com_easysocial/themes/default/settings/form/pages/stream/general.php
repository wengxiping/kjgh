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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS_FEATURES'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ALLOW_BOOKMARKS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.bookmarks.enabled', $this->config->get('stream.bookmarks.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_PIN_ENABLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.pin.enabled', $this->config->get('stream.pin.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_INCLUDE_PRIVATE_CLUSTERS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.clusters.private', $this->config->get('stream.clusters.private')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_DISPLAY_RSS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.rss.enabled', $this->config->get('stream.rss.enabled')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_EXCLUDE_SITE_ADMIN'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.exclude.admin', $this->config->get('stream.exclude.admin')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ARCHIVE_ENABLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.archive.enabled', $this->config->get('stream.archive.enabled'), '', 'data-archive-enable'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('stream.archive.enabled') ? '' : 't-hidden';?>" data-archive-stream-setting>
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ARCHIVE_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.archive.duration', $this->config->get('stream.archive.duration'), array(
								array('value' => '3', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_3_MONTHS'),
								array('value' => '6', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_6_MONTHS'),
								array('value' => '12', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_12_MONTHS'),
								array('value' => '18', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_18_MONTHS'),
								array('value' => '24', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_24_MONTHS')
							)); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('stream.archive.enabled') ? '' : 't-hidden';?>" data-archive-stream-setting>
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_ARCHIVE_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.archive.limit', $this->config->get('stream.archive.limit'), array(
								array('value' => '50', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_50_ITEMS'),
								array('value' => '100', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_100_ITEMS'),
								array('value' => '250', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_250_ITEMS'),
								array('value' => '500', 'text' => 'COM_ES_STREAM_SETTINGS_ARCHIVE_500_ITEMS'),
							)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_PUSHTOP_REACTIONS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.pushtop.reactions', $this->config->get('stream.pushtop.reactions')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.filter.hashtag', $this->config->get('stream.filter.hashtag'), array(
								array('value' => 'or', 'text' => 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS_TYPE_OR'),
								array('value' => 'and', 'text' => 'COM_ES_STREAM_SETTING_FILTER_HASHTAGS_TYPE_AND')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_STREAM_PAGINATION'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_AUTO_LOAD_WHEN_SCROLL'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.pagination.autoload', $this->config->get('stream.pagination.autoload')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_STREAM_SETTINGS_ORDERING'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'stream.pagination.ordering', $this->config->get('stream.pagination.ordering'), array(
							array('value' => 'modified', 'text' => JText::_('COM_ES_STREAM_ORDERING_MODIFIED')),
							array('value' => 'created', 'text' => JText::_('COM_ES_STREAM_ORDERING_CREATED'))
						)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_DATA_FETCH_LIMIT'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.pagination', 'stream.pagination.pagelimit'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_STREAM_SETTINGS_TRANSLATIONS'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ENABLE_AZURE_TRANSLATIONS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.translations.azure', $this->config->get('stream.translations.azure')); ?>

						<div>
							<a href="https://stackideas.com/docs/easysocial/administrators/configuration/stream-translation" target="_blank"><?php echo JText::_('Setup Guide');?></a>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_AZURE_KEY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'stream.translations.azurekey', $this->config->get('stream.translations.azurekey')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_STREAM_SETTINGS_ALWAYS_SHOW_TRANSLATIONS_LINK'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'stream.translations.explicit', $this->config->get('stream.translations.explicit')); ?>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>
