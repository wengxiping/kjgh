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
			<?php echo $this->html('panel.heading', 'COM_ES_BUTTON_COLOURS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.colorpicker', 'button.primary.bg', 'COM_ES_PRIMARY_BUTTON_BG', '', '#4A90E2'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.primary.text', 'COM_ES_PRIMARY_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.success.bg', 'COM_ES_SUCCESS_BUTTON_BG', '', '#4FC251'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.success.text', 'COM_ES_SUCCESS_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.danger.bg', 'COM_ES_DANGER_BUTTON_BG', '', '#F65B5B'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.danger.text', 'COM_ES_DANGER_BUTTON_TEXT', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.standard.bg', 'COM_ES_STANDARD_BUTTON_BG', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'button.standard.text', 'COM_ES_STANDARD_BUTTON_TEXT', '', '#333333'); ?>
			</div>
		</div>
		
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_BUTTON_COLOURS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_AVATAR_STYLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'layout.avatar.style', $this->config->get('layout.avatar.style'), array(
								array('value' => 'rounded', 'text' => 'Rounded'),
								array('value' => 'square', 'text' => 'Square')
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
