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
$rout	=	JFactory::getApplication()->input->get('rout');
$task	=	JFactory::getApplication()->input->get('task');

if ($rout == 'preview')
{
?>
	 <br /><h3 >Message Preview</h3>
<?php
	echo $this->preview_data;
}

if ($rout== 'unsubscribe')
{
   $msg = JText::_('UNSUB_CLICK_MSG');

	echo '<div class="unsub_box">';
	echo '<div class="unsub_msg"><p>';
	echo $msg;
	echo '</p></div><button onclick="window.close();">Close this Window</button>';
	echo '</div>';
}








