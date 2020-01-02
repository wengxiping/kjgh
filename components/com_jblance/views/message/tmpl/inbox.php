<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/tmpl/inbox.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Inbox of Private Messages (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 JHtml::_('jquery.framework');
 
 $doc = JFactory::getDocument();
 $doc->addScript("components/com_jblance/js/utility.js");
 
 $user = JFactory::getUser();
 
 $config 		= JblanceHelper::getConfig();
 $dformat 		= $config->dateFormat;
 $showUsername 	= $config->showUsername;
 
 $nameOrUsername = ($showUsername) ? 'username' : 'name';
 $link_compose = JRoute::_('index.php?option=com_jblance&view=message&layout=compose');
 
 JblanceHelper::setJoomBriToken();
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="userForm">
	<div class="pull-right"><a href="<?php echo $link_compose; ?>" class="btn btn-primary"><span><?php echo JText::_('COM_JBLANCE_COMPOSE'); ?></span></a></div>
	<div class="sp10">&nbsp;</div>
	<div class="jbl_h3title"><?php echo JText::_('COM_JBLANCE_PRIVATE_MESSAGES'); ?></div>
	<?php
	if(count($this->msgs) > 0){		//Called if there are no messages -> Shows a text that spreads over the whole table
	?>
	<table class="table table-hover table-condensed">

		<tbody>
		<?php
		for ($i=0, $x=count($this->msgs); $i < $x; $i++){
			$msg = $this->msgs[$i];
			$userFrom = JFactory::getUser($msg->idFrom);
			$userTo = JFactory::getUser($msg->idTo);
			
			//if the current user is different, then show that name
			if($user->id == $msg->idFrom)
				$userInfo = JFactory::getUser($msg->idTo);
			else 
				$userInfo = JFactory::getUser($msg->idFrom);
			
			$link_read = JRoute::_('index.php?option=com_jblance&view=message&layout=read&id='.$msg->id);
			
			$newMsg = JblanceHelper::countUnreadMsg($msg->id);
		?>
			<tr id="jbl_feed_item_<?php echo $msg->id; ?>">
		  		<td><a href="<?php echo $link_read; ?>"><?php echo $userInfo->$nameOrUsername; ?></a></td>
				<td><a href="<?php echo $link_read; ?>"><?php echo ($msg->approved == 1) ? $msg->subject : '<small>'.JText::_('COM_JBLANCE_PRIVATE_MESSAGE_WAITING_FOR_MODERATION').'</small>'; ?> <?php echo ($newMsg > 0) ? '<span class="label label-info">'.JText::sprintf('COM_JBLANCE_COUNT_NEW', $newMsg).'</span>' : ''; ?></a></td>
				<td nowrap="nowrap"><?php echo JHtml::_('date', $msg->date_sent, $dformat, true);?></td>
				<td>
					<span id="feed_hide_<?php echo $msg->id; ?>" class="help-inline">
						<a class="btn btn-mini" onclick="processMessage('<?php echo $msg->id; ?>', 'message.processmessage');" title="<?php echo JText::_('COM_JBLANCE_REMOVE'); ?>" href="javascript:void(0);"><i class="jbf-icon-remove"></i></a>
					</span>
				</td>
			</tr>
		<?php 
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
					<div class="pagination pagination-centered clearfix">
						<div class="display-limit pull-right">
							<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
							<?php echo $this->pageNav->getLimitBox(); ?>
						</div>
						<?php echo $this->pageNav->getPagesLinks(); ?>
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php 
	}
	else 
		echo "<div class=\"alert alert-info\">".JText::_("COM_JBLANCE_NO_MESSAGES")."</div>";
	?>
</form>
