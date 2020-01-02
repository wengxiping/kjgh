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
	<form method="POST" name="guest_info" id="guest_info">
		<div class="clearfix">&nbsp;</div>
		<div class="row">
			<div class="form-group col-sm-12 col-md-4">
				<label for="guest_name" title="<?php echo JText::_('INV_GUEST_NAME'); ?>">
				<h4><?php echo JText::_('INV_GUEST_NAME');?></h4>
				</label>
				<input type="text" id="guest_name" class="form-control" placeholder="<?php echo JText::_('INV_GUEST_NAME');?>" value='' />
			</div>
		</div>
		<div class="form-group">
			<label for="city" title="<?php echo JText::_('INV_ENTER_CAPTCHA');?>">
			<h4><?php echo JText::_('INV_ENTER_CAPTCHA');?></h4>
			</label>
			<div class="invitex_captcha">
				<?php echo JCaptcha::getInstance(JFactory::getConfig()->get('captcha'))->display('recaptcha', 'recaptcha', 'g-recaptcha'); ?>
			</div>
		</div>
	</form>
</div>
