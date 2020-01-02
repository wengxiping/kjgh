<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );
JHtml::_( 'behavior.modal');

if (JFolder::exists(JPATH_SITE . "/components/com_community"))
{
	require_once JPATH_SITE."/components/com_community/libraries/core.php";
}

$mainframe = JFactory::getApplication();
$root_url = JUri::root();
$document = JFactory::getDocument();
$path = $this->invhelperObj->getViewpath('invites','default_menu');
include $path;

if ($this->oluser || $this->isguest==1)
{
?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
<!--Start STEPS Import,Select Friends,Add Friends-->
	<div class="tab-content">
		<div id="steps_div" >
			<?php
			// Do not show steps if
			if (empty($this->show_compact_view))
			{
				$path = $this->invhelperObj->getViewpath('invites','default_steps');
				include $path;
			}
			?>
		</div>
		<!--END STEPS Import,Select Friends,Add Friends-->
		<div class="invitex_black_n_white">
		<?php
			$path = $this->invhelperObj->getViewpath('invites','default_select_invite_method');
			include $path;
		// Do not show Detailed view if rendered in easysocial APP
		if ($this->user_is_a_guest)
		{
			$path = $this->invhelperObj->getViewpath('invites', 'default_guest');
			include $path;
		}
		?>
			<div class="row-fluid">
				<?php
				$this->show_api = $show_api;
				$this->active_tab = $active_tab;
				$this->img_name = $img_name;
				$this->api_used = $api_used;
				$this->message_type = $message_type;
				// End Assign Variables which are used in templates

				foreach ($this->invite_methods as $ind => $method)
				{
					$this->invmethod = $method;
					$this->ind = $ind;

					if ($method == 'social_apis' or $method == 'email_apis')
					{
					?>
					<div id="invite_apis_form" style="<?php echo ($this->show_api==1)? 'display:block':'display:none'?>" class="tab-pane" name="tab-pane-div">
					<?php
						$path = $this->invhelperObj->getViewpath('invites','default_black_white_social_email');

						if (file_exists($path))
						{
							include $path;
						}

						$this->show_api=100;
					?>
					</div>
					<?php
					}
					else
					{
					?>
					<div id="<?php echo $this->invmethod;?>" class="tab-pane" style="<?php echo ($active_tab==$this->invmethod)?'display:block':'display:none'?>">
						<?php
							// load templates like manual,csv,other
							$path = $this->invhelperObj->getViewpath('invites','default_' . $method);

							if (file_exists($path))
							{
								include $path;
							}
						?>
					</div>
					<?php
					}
					?>
				<?php
				}
				?>

				<div id="js_messaging"  style="<?php echo ($active_tab=='js_messaging')?'display:block':'display:none'?>">
				</div>
			</div><!--tab-content -->
		</div><!--tabbable -->
	</div><!--black n white layout div-->
<?php
	if (JFactory::getApplication()->input->get('invite_anywhere') != '1')
	{
		$path = $this->invhelperObj->getViewpath('invites', 'default_footer');
		include $path;
	}
?>
</div>
<?php
}
else
{
$title = JText::_('LOGIN_TITLE');?>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="page-header"><h2><?php echo $title?></h2></div>
		<div class="invitex_content" id="invitex_content">
			<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}
?>
