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
			<?php echo $this->html('panel.heading', 'COM_ES_TOOLBAR'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'general.layout.toolbar', 'COM_EASYSOCIAL_GENERAL_SETTINGS_TOOLBAR'); ?>
				<?php echo $this->html('settings.toggle', 'general.layout.toolbarsearch', 'COM_EASYSOCIAL_GENERAL_SETTINGS_TOOLBAR_DISPLAY_SEARCH'); ?>
				<?php echo $this->html('settings.toggle', 'general.layout.toolbarguests', 'COM_EASYSOCIAL_GENERAL_SETTINGS_TOOLBAR_DISPLAY_FOR_GUESTS'); ?>
				<?php echo $this->html('settings.toggle', 'general.layout.toolbarsearchguests', 'COM_ES_GENERAL_SETTINGS_TOOLBAR_DISPLAY_SEARCH_FOR_GUESTS'); ?>
				<?php echo $this->html('settings.toggle', 'general.layout.toolbareasyblog', 'COM_ES_DISPLAY_EASYBLOG'); ?>
				<?php echo $this->html('settings.toggle', 'general.layout.toolbareasydiscuss', 'COM_ES_DISPLAY_EASYDISCUSS'); ?>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_TOOLBAR_STYLE'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.colorpicker', 'general.layout.toolbarcolor', 'COM_ES_TOOLBAR_COLOR', '', '#333333'); ?>
				<?php echo $this->html('settings.colorpicker', 'general.layout.toolbaractivecolor', 'COM_ES_TOOLBAR_ACTIVE_COLOR', '', '#5C5C5C'); ?>
				<?php echo $this->html('settings.colorpicker', 'general.layout.toolbartextcolor', 'COM_ES_TOOLBAR_TEXT_COLOR', '', '#FFFFFF'); ?>
				<?php echo $this->html('settings.colorpicker', 'general.layout.toolbarbordercolor', 'COM_ES_TOOLBAR_BORDER_COLOR', '', '#333333'); ?>
			</div>
		</div>
	</div>
</div>
