<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<dialog>
	<width>500</width>
	<height>350</height>
	<selectors type="json">
	{
		"{closeButton}"  : "[data-close-button]",
		"{banButton}" : "[data-ban-button]",
		"{periodInput}" : "[data-ban-period]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{closeButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_DIALOG_BAN_USER_TITLE'); ?></title>
	<content>
		<form>
			<p>
				<?php echo JText::_( 'COM_EASYSOCIAL_PROFILE_ADMINTOOL_BAN_CONFIRMATION' ); ?> <br /><br />
				<textarea name="reason" class="es-ban-reason" placeholder="<?php echo JText::_('COM_ES_PROFILE_BANNED_REASON_PLACEHOLDER'); ?>" style="width:465px; height:150px;" data-ban-reason data-required-error="<?php echo JText::_('COM_ES_PROFILE_BANNED_REASON_REQUIRED_MESSAGE'); ?>"></textarea>
				<br />
				<div class="o-alert o-alert--warning o-alert--icon t-hidden" role="alert" data-composer-notice></div>
				<br />
				<input type="text" value="0" name="period" class="input-sm input-short text-center" style="width:50px !important;" data-ban-period /> <?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_BAN_MINUTES');?>
			</p>
		</form>
	</content>
	<buttons>
		<button data-close-button type="button" class="btn btn-sm btn-es"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-ban-button type="button" class="btn btn-sm btn-es-danger"><?php echo JText::_('COM_EASYSOCIAL_PROFILE_ADMINTOOL_BAN_BUTTON'); ?>
			<div data-ban-button-loader class="o-loader o-loader--sm o-loader--inline"></div>
		</button>
	</buttons>
</dialog>
