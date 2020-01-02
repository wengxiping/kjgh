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

$invURL = $this->invhelperObj->getinviteURL();

if (strpos($invURL, '?') !== false)
{
	$invURL .= "&method_of_invite=invite_by_url";
}
else
{
	$invURL .= "?method_of_invite=invite_by_url";
}

$invURL = $this->invhelperObj->givShortURL($invURL);

?>

<form class="form-horizontal">
	<div class="alert alert-info">
		<?php echo JText::_('INVIT_URL_DES');?>
	</div>
	<div class="control-group">
			<label for="invite_url" class="control-label"><?php echo JHtml::tooltip(JText::_('COM_INVITEX_INV_URL_LABEL_TOOLTIP'), JText::_('INV_URL_LABLE'), '', JText::_('INV_URL_LABLE'));?></label>
			<div class="controls">
				<input readonly="true" id="invite_url" class="invite_url_show input input-xlarge" name="invite_url" value="<?php echo $invURL; ?>" onclick="this.select();">
			</div>
	</div>
</form>
