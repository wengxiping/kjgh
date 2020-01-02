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
			<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_SETTINGS_ITEM_LAYOUT_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DISPLAY_RECENT_AUDIOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.layout.item.recent', $this->config->get('audio.layout.item.recent')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_TOTAL_OTHER_AUDIOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'audio.layout.item.total', $this->config->get('audio.layout.item.total'), '', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_HITS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.layout.item.hits', $this->config->get('audio.layout.item.hits')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.layout.item.duration', $this->config->get('audio.layout.item.duration')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_USER_TAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.layout.item.usertags', $this->config->get('audio.layout.item.usertags')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AUDIO_SETTINGS_DISPLAY_AUDIO_TAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'audio.layout.item.tags', $this->config->get('audio.layout.item.tags')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
