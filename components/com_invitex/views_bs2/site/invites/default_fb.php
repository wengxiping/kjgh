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
$session = JFactory::getSession();
$document = JFactory::getDocument();
$this->img_path = JURI::root() . "media/com_invitex/images/methods";

if ($this->invitex_params->get("invite_apis"))
{
	$invite_apis = $this->invitex_params->get("invite_apis");
}

if($this->oluser || $this->isguest==1)
{
?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="row-fluid">
		<!--Start STEPS Import,Select Friends,Add Friends-->
		<div id="steps_div" >
			<?php
				$path=$this->invhelperObj->getViewpath('invites','default_steps');
				include $path;
				?>
		</div>
		<!--END STEPS Import,Select Friends,Add Friends-->
		<!--FOR GUEST USER...SHOWN CAPTCHA-->
		<?php
			if($this->user_is_a_guest)
			{
				//echo $this->loadTemplate('guest');
				$path=$this->invhelperObj->getViewpath('invites','default_guest');
				include $path;
			}
			?>
		<!--END FOR GUEST USER...SHOWN CAPTCHA-->
		<?php
			if($this->invitex_params->get('inv_look')==1)
			{
			?>
				<ul class="invitex_ul" >
					<?php
						$this->style='';
						$this->classnm='invitex_li';
						foreach($this->invite_methods as  $ind=>$method)
						{
							$this->invmethod=$method;
							$this->ind=$ind;
							if($ind==0)
							{
								$style=$this->style='border-width:0;';
								$class=$this->classnm='invitex_active_li';
							}
							else
							{
								$style=$this->style='';
								$class=$this->classnm='invitex_li';
							}

							?>
							<li class="<?php echo $class; ?>" style="<?php echo $style; ?>" id="<?php echo $method ?>" name="inv_methods" onclick="display_method(this.id);">
								<div>
									<div class="first_div">
										<img class="invitex_fb_image" src="<?php echo $this->img_path."/".$method.'.png';?>" />
										<div ><?php echo JText::_('INV_METHOD_'.strtoupper($method));?></div>
									</div>
									<div id="<?php echo $method ?>_content" style="<?php echo ($ind==0)?'display:block':'display:none'?>" class="inner_div">
										<?php
											if($this->invmethod=='social_apis' or $this->invmethod=='email_apis')
											{
												$path=$this->invhelperObj->getViewpath('invites','default_social_email');

											}
											else
											{
												$path=$this->invhelperObj->getViewpath('invites','default_'.$this->invmethod);

											}
											include $path;
											?>
									</div>
								</div>
							</li>
						<?php
						}
						?>
				</ul>
	</div>
</div>
<?php if(isset($html))
	{
		if(isset($this->oluser->groups['Super Users']) || isset($this->oluser->groups['Administrator']) || $this->oluser->usertype == "deprecated" )
		{
			echo "<b>Note:Following plugins are Not configured properly.Please Provide App key and Secret key for them:</b><br />";
			echo $html;
		}
	}

		if(JFactory::getApplication()->input->get('inv_redirect')=='manual')
		{
			?>
			<script>display_inv_method('inv_method_1');</script>
			<?php
		}

		if(JFactory::getApplication()->input->get('inv_redirect')=='OI_import')
		{
				if($session->get('import_type')=='email')
				{
					?>
					<script>display_inv_method('inv_method_2');</script>
					<?php
				}
				else
				{
					?>
					<script>display_inv_method('inv_method_3');</script>
					<?php
				}
		}

		if(JFactory::getApplication()->input->get('inv_redirect')=='other_tools')
		{
			?>
			<script>display_inv_method('inv_method_4');</script>
			<?php
		}
	}

	if (JFactory::getApplication()->input->get('invite_anywhere')!='1')
	{
		$path = $this->invhelperObj->getViewpath('invites','default_footer');
		include $path;
	}
}
else
{
$title=JText::_('LOGIN_TITLE');?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="page-header">
		<h2><?php echo $title?></h2>
	</div>
	<div class="invitex_content" id="invitex_content">
		<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}
?>
