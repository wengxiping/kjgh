<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
$mainframe = JFactory::getApplication();
?>
<div class="inv_guest">
	<form class="form-horizontal"  method="POST" name="guest_info" id="guest_info">
		<div class="control-group">
			<label class="control-label" for="guest_name" title="<?php echo JText::_('INV_GUEST_NAME'); ?>">
			<?php echo JText::_('INV_GUEST_NAME');?>
			</label>
			<div class="controls">
				<input type="text" id="guest_name" class="input guest_name_post" placeholder="<?php echo JText::_('INV_GUEST_NAME');?>" value='' />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="city" title="<?php echo JText::_('INV_ENTER_CAPTCHA');?>">
			<?php echo JText::_('INV_ENTER_CAPTCHA');?>
			</label>
			<div class="controls span4">
				<?php echo JCaptcha::getInstance(JFactory::getConfig()->get('captcha'))->display('recaptcha', 'recaptcha', 'g-recaptcha'); ?>
			</div>
		</div>
	</form>
</div>
