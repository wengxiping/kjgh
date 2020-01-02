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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_ITEM_LAYOUT_GENERAL'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_DISPLAY_EMBED_LINK'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.item.embed', $this->config->get('video.layout.item.embed')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_DISPLAY_RECENT_VIDEOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'video.layout.item.recent', $this->config->get('video.layout.item.recent'), array(
								array('value' => SOCIAL_VIDEO_OTHER_NONE, 'text' => 'COM_ES_VIDEOS_SETTINGS_DISPLAY_OTHER_VIDEO_NONE'),
								array('value' => SOCIAL_VIDEO_OTHER_RECENT, 'text' => 'COM_ES_VIDEOS_SETTINGS_DISPLAY_OTHER_VIDEO_RECENT'),
								array('value' => SOCIAL_VIDEO_OTHER_CATEGORY, 'text' => 'COM_ES_VIDEOS_SETTINGS_DISPLAY_OTHER_VIDEO_CATEGORY'),
							)); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_TOTAL_OTHER_VIDEOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'video.layout.item.total', $this->config->get('video.layout.item.total'), '', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_TOTAL_OTHER_VIDEOS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.inputbox', 'video.layout.featured.total', $this->config->get('video.layout.featured.total'), '', array('class' => 'input-short text-center')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_DISPLAY_VIDEO_HITS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.item.hits', $this->config->get('video.layout.item.hits')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_DISPLAY_VIDEO_DURATION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.item.duration', $this->config->get('video.layout.item.duration')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_DISPLAY_VIDEO_USER_TAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.item.usertags', $this->config->get('video.layout.item.usertags')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_VIDEOS_SETTINGS_DISPLAY_VIDEO_TAGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.item.tags', $this->config->get('video.layout.item.tags')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEOS_SETTINGS_DEFAULT_EDITOR'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.editors', 'video.layout.item.editor', $this->config->get('video.layout.item.editor'), '', '', true, true); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_VIDEO_SETTINGS_PLAYER'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_PLAYER_ADD_LOGO'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.player.logo', $this->config->get('video.layout.player.logo'), '', array('data-player-logo')); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('video.layout.player.logo') ? '' : 't-hidden';?>" data-logo-form>
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_PLAYER_LOGO'); ?>

					<div class="col-md-7">
						<img src="<?php echo ES::video()->getPlayerLogo();?>" width="24" height="24" />

						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="video_logo" id="video_logo" class="input" style="width:265px;" data-uniform />
						</div>
						<br />
						<div>Please upload a square image for the logo. Ideal size would be 24px * 24px</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_PLAYER_ADD_WATERMARK'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'video.layout.player.watermark', $this->config->get('video.layout.player.watermark'), '', array('data-player-watermark')); ?>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('video.layout.player.watermark') ? '' : 't-hidden';?>" data-watermark-form>
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_PLAYER_WATERMARK_IMAGE', true, JText::sprintf('COM_ES_VIDEO_PLAYER_WATERMARK_IMAGE_HELP', SOCIAL_VIDEO_WATERMARK_WIDTH, SOCIAL_VIDEO_WATERMARK_HEIGHT)); ?>

					<div class="col-md-7">
						<img src="<?php echo ES::video()->getPlayerWatermark();?>" width="180" />

						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="video_watermark" id="video_watermark" class="input" style="width:265px;" data-uniform />
						</div>
					</div>
				</div>

				<div class="form-group <?php echo $this->config->get('video.layout.player.watermark') ? '' : 't-hidden';?>" data-watermark-form>
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_PLAYER_WATERMARK_POSITION'); ?>

					<div class="col-md-7">
						<select name="video.layout.player.watermarkposition" class="o-form-control">
							<option value="top-left" <?php echo $this->config->get('video.layout.player.watermarkposition') == 'top-left'? ' selected="selected"' : '';?>>Top Left</option>
							<option value="top-right" <?php echo $this->config->get('video.layout.player.watermarkposition') == 'top-right'? ' selected="selected"' : '';?>>Top Right</option>
							<option value="bottom-left" <?php echo $this->config->get('video.layout.player.watermarkposition') == 'bottom-left'? ' selected="selected"' : '';?>>Bottom Left</option>
							<option value="bottom-right" <?php echo $this->config->get('video.layout.player.watermarkposition') == 'bottom-right'? ' selected="selected"' : '';?>>Bottom Right</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
