<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
$input = JFactory::getApplication()->input;
$action	=	$input->get('action','','STRING');

if($action == 'confirm')
{
	if($input->get('refid','','STRING') != '')
	{
		$var = 'refid';
		$value = $input->get('refid','','STRING');
	}
	else
	{
		$var = 'refemail';
		$value = $input->get('refemail','','STRING');
	}

	?>
<div class="well well-small before_unsub">
	<div class="alert alert-info"><?php echo JText::_('COM_INVITEX_UNSUB_AFFECT_MSG');?></div>
	<button class="btn btn-danger" onclick="unsubscribe('<?php echo $var;?>','<?php echo $value?>');"><?php echo JText::_('COM_INVITEX_UNSUB');?></button>
	<button class="btn " onclick="window.close();"><?php echo JText::_('COM_INVITEX_CANCEL');?></button>
</div>
<div class="well well-small after_unsub">
	<div class="alert alert-success"><?php echo JText::_('COM_INVITEX_UNSUB_SUCCESS');?></div>
	<button class="btn " onclick="window.close();"><?php echo JText::_('COM_INVITEX_CLOSE');?></button>
</div>
<?php
return;
}
