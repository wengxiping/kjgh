<?php
/**
 * @version    SVN: <svn_id>
 * @package    InviteX
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.filesystem.folder');
$session = JFactory::getSession();
$document=JFactory::getDocument();
$itemid = $this->itemid;
$onload_redirect=JRoute::_('index.php?option=com_invitex&view=invites&Itemid='.$itemid,false);

JHtml::_('behavior.tooltip');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$input = JFactory::getApplication()->input;
$cid   = $input->get( 'cid', '', 'ARRAY');
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order !== '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}

	function submit_resendform()
	{
		document.getElementById( "task" ).value='';
		document.adminForm.submit();
	}

	function chk_resend()
	{
		document.getElementById( "task" ).value='resend';
		var count	=	document.getElementById( "count" ).value
		var maxics	=	document.getElementById( "maxics" ).value
		var i,j = 0;

		j= jQuery(".contacts:checked").length;
		if( maxics <j )
			alert('you can\'t submit more then ' + maxics + ' invitation')
		else if( !j )
			alert('Please select at least one email-id to send invites.')
		else
			document.adminForm.submit();
	}

	function toggleAll(element)
	{
		var form = document.forms.adminForm

		for(z	= 0; z	< (form.length); z++)
		{
			if(form[z].type == 'checkbox')
				form[z].checked = element.checked
			}
	}
</script>
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invitex_title">
		<h2><?php echo JText::_('RE_SEND')?></h2>
	</div>
	<div>
		<div class="invitex_skip text-right">
			<button class="btn" onclick='window.location="<?php echo $onload_redirect?>"'><?php echo JText::_('BACK_TO_INVITEX');?></button>
		</div>
		<br><br>
	</div>
	<form action='' method=post name="adminForm" id="adminForm">
		<div id="filter-bar" class="">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="button" onclick="submit_resendform()" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="icon-search"></i>
				</button>
				<button type="button" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();">
				<i class="icon-remove"></i>
				</button>
			</div>
			<?php if (JVERSION >= '3.0') : ?>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible">
				<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone hidden-tablet">
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
			<div class="btn-group pull-right hidden-phone hidden-tablet">
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
		<div class="clearfix">&nbsp;</div>
		<div class="clearfix">&nbsp;</div>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php
			else : ?>
		<div class="clearfix">&nbsp;</div>
		<table class="table-condensed table-striped table-bordered table-responsive " width="100%">
			<thead>
				<tr>
					<th width="5%" class="text-left wordsbreak"><input type="checkbox" onclick="toggleAll(this)" name="toggle_all" title="Select/Deselect all" /></th>
					<th class="text-left wordsbreak"><?php echo JHTML::_( 'grid.sort',  'NAMES', 'iie.invitee_name', $listDirn, $listOrder); ?></th>
					<th class="text-left wordsbreak"><?php echo JHTML::_( 'grid.sort',  'EMAILS', 'iie.invitee_email', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<?php
				if($this->items)
				{

					foreach ( $this->items as $row )
					{

						$mail	=	trim($row->invitee_email);
						echo "<tr><td width='5%'>
						<input name='contacts[".$row->invitee_name."]' class='contacts' value='".$mail."' type='checkbox' class='thCheckbox'/>
						<td class='wordsbreak'>".$row ->invitee_name."</td>
						<td class='wordsbreak'>".$mail."</td>
						</tr>";
					}

				}
				?>
		</table>
		<?php if (JVERSION >= '3.0'): ?>
		<?php echo $this->pagination->getListFooter(); ?>
		<?php else: ?>
		<div class="pager">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>
		<?php endif; ?>
		<?php endif; ?>
		<input type="hidden" name="option" value="com_invitex"/>
		<input type="hidden" name="controller" value="resend"/>
		<input type="hidden" id="task" name="task" value=""/>
		<input type="hidden" name="count" value="<?php if(!empty($counter)) echo  $counter; ?>" id="count"/>
		<input type="hidden" name="maxics" value="<?php  echo $this->invitex_params->get('global_value'); ?>" id="maxics"/>
		<input type="hidden" name="resend" value="1"/>
		<input type="hidden" id="filter_order" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" id="filter_order_Dir" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="boxchecked" value="" />
		<?php echo JHTML::_( 'form.token' );
			if($this->items)
			{?>
		<div class="form-actions" align="center">
			<input type="button" name="send" value="<?php echo JText::_('RE_SEND_BUTTON_TEXT')?>" class="btn btn-primary btn-large" onclick="chk_resend('resend');"/>
		</div>
		<?php
			}
			?>
	</form>
</div>
<?php
	$path=$this->invhelperObj->getViewpath('invites','default_footer');
	include $path;
?>
