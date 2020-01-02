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

$input           = JFactory::getApplication()->input;
$this->view_nm   = $input->get('view');
$this->option_nm = $input->get('option');

if (empty($this->icon_used))
{
	$this->icon_used = 'black_white';
}

$this->img_path = JUri::root() . "media/com_invitex/images/methods/icons/" . $this->icon_used . "/";
?>
<!--START Icons to Select APIS -->
<div class=" top_div_icon" >
	<div class="thumbnails icon_ul  row-fluid">
		<?php
			$api_used       = '';
			$message_type   = '';
			$img_name       = '';
			$show_api       = 0;
			$class          = '';
			$active_tab     = '';
			$img_name       = '';
			$li_count       = 1;
			$result_con_all = $this->renderAPIicons;

			foreach ($this->invite_methods as $ind => $method)
			{
				$result    = array();
				$img_class = "inv_select_invite_img";

				// Load Social API's Like Facebook,Twitter,Linkedin
				if (!empty($this->invite_apis))
				{
					if (($method == 'social_apis' or $method == 'email_apis' or $method == 'sms_apis'))
					{
						if (!empty($result_con_all[$method]))
						{
							$result  = $result_con_all[$method];
							$api_cnt = 1;
							$cnt     = 0;

							for ($i = 0; $i < count($result); $i++)
							{
								$img_class = "inv_select_invite_img";

								if (isset($result[$i]['message_type']) && ($result[$i]['message_type'] == 'pm' or $result[$i]['message_type'] == 'sms' or  $result[$i]['message_type'] == 'email'))
								{
									$form_name = $result[$i]['name'] . '_connect_form';
									$class     = '';

									if ($li_count == 1)
									{
										$api_used             = $result[$i]['api_used'];
										$message_type         = $result[$i]['message_type'];
										$img_name             = $result[$i]['img_file_name'];
										$active_tab           = $form_name;
										$img_class            = "inv_selected_method_active";
										$this->method_tooltip = $result[$i]['name'];
										$show_api             = 1;
									}
									?>
									<?php // Start - Added in v2.9.7 ?>
									<?php
										if (isset($result[$i]['use_plugin_html']))
										{
											// Add javascript passed by plugin
											echo $result[$i]['scripts'];

											// IMP: Start - Add script to emulate first invite method is selected/clicked
											// Check if FB is being shown as first option
											if ($li_count == 1)
											{
												$initFBdiv = "techjoomla.jQuery(document).ready(function(){ var spanEle = document.getElementById('list_invitex_li1'); display_api(spanEle, '" . $form_name . "', '" . $this->img_path . "', '" .  $result[$i]['img_file_name'] . "', '" . $result[$i]['api_used'] . "', '" . $result[$i]['message_type'] . "', '" . $this->user_is_a_guest . "', '" . $result[$i]['name'] . "') });";
												?>
												<script>
													<?php echo $initFBdiv;?>
												</script>
											<?php
											}
										// IMP: End - Add script to emulate first invite method is selected
										}
										?>
									<?php // End - Added in v2.9.7 ?>
									<span class="invitex_li"
										id="list_invitex_li<?php echo $li_count;?>"
										for='<?php echo $form_name;?>'
										name="invite_apis"
										onclick="display_api(this, '<?php echo $form_name; ?>', '<?php echo $this->img_path; ?>', '<?php echo $result[$i]['img_file_name']; ?>', '<?php echo $result[$i]['api_used']; ?>', '<?php echo $result[$i]['message_type']; ?>', <?php echo $this->user_is_a_guest;?>, '<?php echo $result[$i]['name'];?>');">
										<img class="<?php echo $img_class;?>" title="<?php echo $result[$i]['name'];?>" src="<?php echo $this->img_path . "small/" . $result[$i]['img_file_name'];?>" />
									</span>
									<?php
										$li_count++;
								}
							}
						}
					}
				}

				// For other methods like manual,csv,advanced_manual
				if (!($method == 'oi_email' or $method == 'oi_social' or $method == 'social_apis' or $method == 'email_apis' or $method == 'sms_apis'))
				{
					if ($li_count == 1)
					{
						$class      = 'active';
						$active_tab = $method;
						$img_class  = "inv_selected_method_active";

						if (!empty($this->method_tooltip_arr[$method]))
						{
						$this->method_tooltip = $tool_tip_arr[$method];
						}
					}

					$li_count++;
					$link_for_invite_view = JRoute::_('index.php?option=com_invitex&view=invites&layout=black_white&tmpl=component&show_compact_view=1&invite_method=' . $method);
				?>
					<span class="invitex_li " id="list_invitex_li<?php	echo $li_count;	?>" onclick="showinvitebuttondiv(this,'<?php echo $method ?>')">
						<img class="<?php echo $img_class;?>" title="<?php if(!empty($this->tool_tip_arr[$method]))	echo $this->tool_tip_arr[$method] ?>" src="<?php echo JUri::root() . 'media/com_invitex/images/methods/icons/' . $this->icon_used . '/small/' . $method . '.png';?>" alt="">
					</span>
				<?php
				}
			}
			?>
	</div>
	<?php
		if ((in_array('oi_email', $this->invite_methods)) || in_array('oi_social', $this->invite_methods))
		{
			?>
	<ul class="thumbnails " >
		<li id="list_invitex_li<?php echo $li_count;?>" class="invitex_li" style="float:right">
			<div class="btn-group">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
				<?php echo JText::_('INV_OTHERS'); ?>
				<span class="caret"></span>
				</a>
				<ul class="dropdown-menu">
					<?php
						if (in_array('oi_email', $this->invite_methods))
						{
							?>
					<li class="inv_other_mmethods_li" title="<?php echo JText::_('INV_METHOD_OI_EMAIL'); ?>" onclick="showinvitebuttondiv(this,'oi_email','<?php echo JText::_('INV_METHOD_OI_EMAIL'); ?>')"><?php echo JText::_('INV_METHOD_OI_EMAIL'); ?></li>
					<?php
						}

						if (in_array('oi_social', $this->invite_methods))
						{
							?>
					<li class="inv_other_mmethods_li" title="<?php echo JText::_('INV_METHOD_OI_SOCIAL'); ?>" onclick="showinvitebuttondiv(this,'oi_social','<?php echo JText::_('INV_METHOD_OI_SOCIAL'); ?>')"><?php echo JText::_('INV_METHOD_OI_SOCIAL'); ?></li>
					<?php
						}
						?>
				</ul>
			</div>
		</li>
	</ul>
	<?php
		}
		?>
</div>
<div class="row-fluid">
<h3>
	<?php echo JText::_('INV_METHOD'); ?><b><span class="inv_method_title "></span></b>
</h3>
</div>
<!--END Icons to Select APIS -->
