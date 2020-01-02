<?php
/**
 * @package InviteX
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');
$document = JFactory::getDocument();

?>


<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
		<div class="well well-large center" style="width:60%;margin-left:20%;">
			<span class="label label-important" id="populate_msg"><?php echo JText::_('POPULATE_USERS_MSG');?></span>

			<div><button class="btn btn-success" style="margin-top:20px;" id="populate_button" onclick="populateUsers('<?php echo JText::_('POPULATE_SUCCESS');?>')"><?php echo JText::_('POPULATE_USERS');?></button></div>
		</div>
</div>
