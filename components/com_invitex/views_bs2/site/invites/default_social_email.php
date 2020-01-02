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
?>
<ul class="invitex_api_ul" style="list-style-type:none;">
	<?php
		if ($this->invitex_params->get("invite_apis"))
		{
			if (isset($this->renderAPIicons[$this->invmethod]))
			{
				$result   = $this->renderAPIicons[$this->invmethod];
				$img_path = JUri::root() . "media/com_invitex/images/";
				$api_cnt  = 1;
				$cnt      = 0;

				for ($i = 0; $i < count($result); $i++)
				{
					if (isset($result[$i]['message_type']))
					{
						$form_name = $result[$i]['name'] . '_connect_form';?>
						<li class="invitex_li" onMouseOver="this.style.backgroundColor='#EDEFF4';" onMouseOut="this.style.backgroundColor='#FFFFFF';" id="<?php echo 'api_'.$api_cnt?>" name="invite_apis">
							<?php // Since v2.9.7 added html field in plugin trigger output ?>
							<?php
								if (isset($result[$i]['use_plugin_html'])):
									echo $result[$i]['scripts'];
									echo $result[$i]['html'];
								else:
								?>
							<form class="form-horizontal" name="<?php echo $form_name ?>"  id="<?php echo $form_name ?>" method="POST">
								<a  class="invitex_center" <?php	if($this->user_is_a_guest){ ?> onclick="return(set_guest_name('<?php echo $form_name ?>'))" 	<?php	}	?> href="javascript:document.<?php echo $form_name ?>.submit();" >
									<img class="invitex_image" src="<?php echo $img_path.$result[$i]['img_file_name'] ?>" />
									<div style="vertical-align:middle;"><?php echo $result[$i]['name'];?>
									</div>
								</a>
								<?php echo JHtml::_('form.token'); ?>
								<input type="hidden" name="option" value="com_invitex"/>
								<input type="hidden" name="controller" value="invites"/>
								<input	type="hidden" id="guest"  class="guest_name_post" name="guest"	value='' />
								<input type="hidden" name="task" value="get_request_token"/>
								<input type="hidden" name="api_used" value="<?php echo $result[$i]['api_used'] ?>"/>
								<input type="hidden" name="api_message_type" value="<?php echo $result[$i]['message_type'] ?>">
							</form>
							<?php endif; ?>
							<div class="clearfix"></div>
						</li>
						<?php
						$cnt++;
					}
				}
			}
			else
			{
			?>
				<div class="alert alert-error">
					<?php
						if ($this->invmethod == 'social_apis')
						{
							echo JText::_('NO_SOCIAL_API');
						}
						elseif ($this->invmethod == 'email_apis')
						{
							echo JText::_('NO_EMAIL_API');
						}
						?>
				</div>
		<?php
			}
		}
		?>
</ul>
