<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.filesystem.folder' );
$mainframe = JFactory::getApplication();

if ($this->invitex_params->get("invite_apis"))
{
	$invite_apis = $this->invitex_params->get("invite_apis");
}
?>
<ul class="invitex_api_ul">
	<?php
		if (!empty($invite_apis))
		{
			$result = $this->renderAPIicons;
			$api_cnt = 1;
			$cnt = 0;
			for ($i = 0; $i<count($result); $i++)
			{
				if (isset($result[$i]['message_type']) && $result[$i]['message_type']=='email')
				{
				?>
				<?php $form_name=$result[$i]['name'].'_connect_form';?>
				<li class="invitex_li" onMouseOver="this.style.backgroundColor='#EDEFF4';" onMouseOut="this.style.backgroundColor='#FFFFFF';" id="<?php echo 'api_'.$api_cnt?>" name="invite_apis">
					<form class="form-horizontal" name="<?php echo $form_name ?>" id="<?php echo $form_name ?>" method="POST">
						<a  class="invitex_center" <?php	if ($this->user_is_a_guest){ ?> onclick="return(set_guest_name('<?php echo $form_name ?>'))" 	<?php	}	?> href="javascript:document.<?php echo $form_name ?>.submit();">
						<img class="invitex_image" src="<?php echo $this->img_path."/".$result[$i]['img_file_name'] ?>" />
						<span><?php echo $result[$i]['name'];?></span>
						</a>
						<?php echo JHtml::_('form.token'); ?>
						<input type="hidden" name="option" value="com_invitex"/>
						<input type="hidden" name="controller" value="invites"/>
						<input	type="hidden" id="guest" name="guest"  class="guest_name_post"	value='' />
						<input type="hidden" name="task" value="get_request_token"/>
						<input type="hidden" name="api_used" value="<?php echo $result[$i]['api_used'] ?>"/>
						<input type="hidden" name="api_message_type" value="<?php echo $result[$i]['message_type'] ?>">
					</form>
					<div class="clearfix"></div>
				</li>
				<?php
					$cnt++;
				}
			}

			if ($cnt == 0)
			{
			?>
				<div class="alert alert-danger">
					<?php echo JText::_('NO_EMAIL_API');?>
				</div>
		<?php
			}
		}
		?>
</ul>
