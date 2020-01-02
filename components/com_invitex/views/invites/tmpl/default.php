<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

if (version_compare(JVERSION, '3.0', 'gt'))
{
	JHtml::_('behavior.framework');
}
else
{
	JHtml::_('behavior.mootools');
}

jimport( 'joomla.filesystem.folder' );

$mainframe = JFactory::getApplication();
$session = JFactory::getSession();
$document = JFactory::getDocument();

if ($this->invitex_params->get("invite_apis"))
{
	$invite_apis=$this->invitex_params->get("invite_apis");
}

if (JFolder::exists(JPATH_SITE . "/components/com_community/"))
{
	require_once JPATH_SITE."/components/com_community/libraries/core.php";
}

$isguest = $this->invitex_params->get('guest_invitation');

if($this->oluser || $isguest == 1)
{
?>
<!--FOR GUEST USER...SHOWN CAPTCHA-->
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div>
	<div id="steps_div" >
			<?php
				$path = $this->invhelperObj->getViewpath('invites','default_steps');
				include $path;
			?>
	</div>
	<?php
	if (!$this->invitex_params->get('inv_look'))
	{
	?>
		<div class=" tabbable tabs-left inv_tabbable row">
			<ul class="nav nav-tabs inv-tabs col-xs-12 col-sm-4">
				<?php
					$this->classnm = '';
					$active_tab = '';
					$invite_methods = $this->invite_methods;

					foreach ($this->invite_methods as $this->ind => $this->invmethod)
					{
						if ($this->ind == 0)
						{
							$this->classnm = 'active';
							$active_tab = $this->invmethod;
						}
						else
						{
							$this->classnm = '';
						}
						?>
						<li class="<?php echo $this->classnm;?>" onclick="showinvitebuttondiv(this,'<?php echo $this->invmethod ?>')">
							<a data-toggle="tab">
								<div class="first_div">
									<?php
									$icon_for_method = '';

									switch($this->invmethod)
									{
										case'social_apis':
										case'oi_social':
												$icon_for_method = "glyphicon glyphicon-user";
												break;
										case 'sms_apis':
												$icon_for_method = "glyphicon glyphicon-envelope";
												break;
										case 'manual':
										case 'advanced_manual':
												$icon_for_method = "glyphicon glyphicon-pencil";
												break;
										case'email_apis':
										case'oi_email':
										case'js_messaging':
												$icon_for_method = "glyphicon glyphicon-envelope";
												break;
										case'other_tools':
												$icon_for_method = "glyphicon glyphicon-folder-open";
												break;
										case'inv_by_url':
												$icon_for_method = "glyphicon glyphicon-share";
												break;
									}
									?>
									<div>
										<i class="<?php	echo $icon_for_method;	?>"></i>
										<span><?php echo JText::_('INV_METHOD_'.strtoupper($this->invmethod));?></span>
									</div>
								</div>
							</a>
						</li>
				<?php } ?>
			</ul>
			<div class="tab-content col-xs-12 col-sm-8">
				<?php
				if ($this->user_is_a_guest)
				{
					$path = $this->invhelperObj->getViewpath('invites', 'default_guest');
					include $path;
				}

				foreach ($this->invite_methods as $this->ind => $this->invmethod)
				{
					$invitex_params = $this->invitex_params;
				?>
				<div id="<?php echo $this->invmethod;?>" class="tab-pane" name="tab-pane-div" style="<?php echo ($active_tab==$this->invmethod)?'display:block':'display:none'?>">
					<?php
					if ($this->invmethod=='social_apis' or $this->invmethod=='email_apis')
					{
						$path = $this->invhelperObj->getViewpath('invites','default_social_email');
					}
					else
					{
						$path = $this->invhelperObj->getViewpath('invites', 'default_'.$this->invmethod);
					}

					include $path;
					?>
				</div>
				<?php
				}
				?>
			</div><!--tab-content -->
		</div>
	</div>
</div>
	<?php
	}//logged in user if closed

	if (JFactory::getApplication()->input->get('invite_anywhere') != '1')
	{
		$path = $this->invhelperObj->getViewpath('invites', 'default_footer');
		include $path;
	}
	?>
<?php
}
else
{
$title=JText::_('LOGIN_TITLE');?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
<div class="page-header"><h2><?php echo $title?></h2></div>
	<div class="invitex_content" id="invitex_content">
		<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}?>
