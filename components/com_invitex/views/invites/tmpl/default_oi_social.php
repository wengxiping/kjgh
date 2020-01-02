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

?>
<form class="form-horizontal"  method="POST" name="socialimportform" id="socialimportform">
	<fieldset>
		<div class="alert alert-info"><?php echo JText::_('IMPORT_MESS') ?></div>
		<div class="form-group">
			<label for="email_box" class="control-label col-lg-2 col-md-2 col-sm-3 col-xs-12"><?php echo JText::_('USER_ID') ?></label>
			<input class="inputbox form-control col-lg-2 col-md-2 col-sm-2 col-xs-12" type="text" name="social_email" id="social_email" value="">
		</div>
		<div class="form-group">
			<label for="password_box" class="control-label col-lg-2 col-md-2 col-sm-3 col-xs-12"><?php echo JText::_('PASS') ?></label>
			<input class="inputbox form-control col-lg-2 col-md-2 col-sm-2 col-xs-12" type="password" name="social_password" id="social_password" value="">
		</div>
		<div class="form-group">
			<label for='provider_box' class="control-label col-lg-2 col-md-2 col-sm-3 col-xs-12"><?php echo JText::_('PROVIDER') ?></label>
			<div>
				<?php
					$i=1;
					foreach($this->oi_services as $type=>$providers)
					{
						if($this->inviter->pluginTypes[$type]=='Social Networks')
						{
							$s="";
							foreach($providers as $provider=>$details)
							{


									if(in_array($details['name'], $this->oi_plugin_selection))
									{
										$options[] = JHTML::_('select.option', $provider, $details['name']);
										?>
				<!--
					<option value="<?php echo $provider; ?>" ><?php echo $details['name'];?></option>
					-->
				<?php
					}


					}
					echo $dropdown = JHTML::_('select.genericlist', $options, 'class="inputbox form-control col-lg-2 col-md-2 col-sm-2 col-xs-12 chzn-done"', 'name', 'value', 'text');

					}
					}
					?>
			</div>
		</div>
		<div class="invitex-form-actions">
			<input class="btn btn-primary " type="button" name="social_import" value="<?php echo JText::_('IMPORT_CON');?>" onclick="upload('social_invitex','socialimportform',<?php	echo $this->user_is_a_guest;	?>);">
		</div>
	</fieldset>
	<div class="clearfix"></div>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="option" value="com_invitex">
	<input type="hidden" name="task" value="sort_mail">
	<input	type="hidden" id="guest" name="guest"  class="guest_name_post"	value='' />
	<input type="hidden" name="rout" id="rout" value="OI_import">
	<input type="hidden" name="import_type" id="import_type" value="social">
</form>
