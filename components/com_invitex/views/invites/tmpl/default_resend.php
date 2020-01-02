<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.filesystem.file');

$mainframe = JFactory::getApplication();
if($this->oluser)
{
	$itemid = $this->itemid;
	$session = JFactory::getSession();
	$onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&Itemid='.$itemid,false);


?>
	<script type='text/javascript'>
		techjoomla.jQuery(document).ready(function() {
			techjoomla.jQuery("#limit").removeClass('inputbox');
			techjoomla.jQuery("#limit").removeClass('input-mini');
			techjoomla.jQuery("#limit").removeAttr('size');
		});
		function chk_resend()
		{
			var count	=	document.getElementById( "count" ).value
			var maxics	=	document.getElementById( "maxics" ).value
			var i,j = 0;

			j= jQuery(".contacts:checked").length;
			if( maxics <j )
				alert('you can\'t submit more then ' + maxics + ' invitation')
			else if( !j )
				alert('Please select at least one email-id to send invites.')
			else
				document.resendform.submit();
		}

		function toggleAll(element)
		{
			var form = document.forms.resendform

			for(z	= 0; z	< (form.length); z++)
			{
				if(form[z].type == 'checkbox')
					form[z].checked = element.checked
			   	}
		}
 	</script>

<?php
//eoc for JS toolbar inclusion
$emails=$this->data;
/* set session vaiable to blank */
$this->invhelperObj->setSession();
?>
<div class="">
	<div class="invitex_title">
		<h2><?php echo JText::_('RE_SEND')?></h2>
		<div class="invitex_skip">
			<button class="btn btn-default" onclick='window.location="<?php echo $onload_redirect?>"'><?php echo JText::_('BACK_TO_INVITEX');?></button>
		</div>
	</div><!-- end of invitex_title div -->

	<form action="" name="resendform" method="post" class="form-horizontal">
		<?php if (JVERSION >= '3.0') : ?>
				<div class="btn-group pull-right hidden-xs">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>

				<div class="btn-group pull-right hidden-xs hidden-sm">
					<label for="directionTable" class="element-invisible">
						<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
					</label>
					<select name="directionTable" id="directionTable"
						class="input-medium" onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
						<option value="asc"
							<?php
								if ($listDirn == 'asc')
								{
									echo 'selected="selected"';
								}
							?>>
								<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
						</option>
						<option value="desc"
							<?php
							if ($listDirn == 'desc')
							{
								echo 'selected="selected"';
							}
							?>>
								<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
						</option>
					</select>
				</div>

				<div class="btn-group pull-right hidden-xs hidden-sm">
					<label for="sortTable" class="element-invisible">
						<?php echo JText::_('JGLOBAL_SORT_BY'); ?>
					</label>
					<select name="sortTable" id="sortTable" class="input-medium"
						onchange="Joomla.orderTable()">
						<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
						<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
					</select>
				</div>

			</div>
				<?php endif; ?>
			<table class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th width="5%"><input type="checkbox" onclick="toggleAll(this)" name="toggle_all" title="Select/Deselect all" checked="checked"/></th>
						<th style="text-align:center"><?php echo JText::_('EMAILS') ?></th>
						<th style="text-align:center"><?php echo JText::_('NAMES') ?></th>
					</tr>
				</thead>

				<?php
				if($this->data)
				{
					foreach($emails as $email)		//start foreach *
					{
						$mail	=	trim($email->invitee_email);
							echo "<tr><td width='5%'>
							<input name='contacts[$email->invitee_name]' class='contacts' value='$mail' type='checkbox' class='thCheckbox' checked=\"checked\"/>
							<td>$mail</td>
							<td>$email->invitee_name</td>
							</tr>";
					}
				}
				else
				{
				?>
				<tr>
					<td colspan="3" class="center">
					 <?php echo JText::_('NO_RESEND'); ?>
					</td>
				</tr>
				<?php
				}
				?>
			<?php if (JVERSION < '3.0'): ?>
			<tfoot>
				<tr>
					<td colspan="3">
						<div class="pager pull-left">
							<?php echo $this->pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
			<?php endif;

		if (JVERSION >= '3.0'):
			echo $this->pagination->getListFooter();
		endif;
	  ?>

		</table>
		<?php
		if($this->data)
		{ ?>
		 <div class="invitex-form-actions">
				<input type="button" name="send" value="<?php echo JText::_('RE_SEND_BUTTON_TEXT')?>" class="btn btn-primary " onclick="chk_resend();"/>
		</div>
		<?php
			}
		 ?>

	</fieldset>
	<input type="hidden" name="option" value="com_invitex"/>
	<input type="hidden" name="controller" value="invites"/>
	<input type="hidden" name="task" value="resend"/>
	<input type="hidden" name="count" value="<?php if(!empty($counter)) echo  $counter; ?>" id="count"/>
	<input type="hidden" name="maxics" value="<?php if(!empty($counter)) echo $this->invitex_params->get('global_value'); ?>" id="maxics"/>
	<input type="hidden" name="resend" value="1"/>
	<input type="hidden" name="boxchecked" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
<?php
	// get the template and default paths for the layout
	if(JFactory::getApplication()->input->get('invite_anywhere')!='1')
	{
		$path=$this->invhelperObj->getViewpath('invites','default_footer');
		include $path;
	}
}
else
{
$title=JText::_('LOGIN_TITLE');
?>
<div class="">
	<div class="page-header"><h2><?php echo $title?></h2></div>
	<div class="invitex_content" id="invitex_content">
		<h3><?php echo JText::_('NON_LOGIN_MSG');?></h3>
	</div>
</div>
<?php
}
?>


