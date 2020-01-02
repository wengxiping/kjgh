<?php
/**
 * @version    SVN: <svn_id>
 * @package    Invitex
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

JHtml::_('behavior.tooltip');

$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');


$input = JFactory::getApplication()->input;
$cid   = $input->get( 'cid', '', 'ARRAY');

$sortFields = $this->getSortFields();

?>
<script type='text/javascript'>
function confirm_remove()
{
		if(jQuery(".checked_data:checked").length==0)
		{
				alert("Please select at least one User ID");
				return false;
		}
		var r = confirm("DO you really want to remove these users from unsubsribe list??");
		if(r)
		{
			document.getElementById('task').value="unsubscribe_list.remove";
			document.adminForm.submit();
		}
		else
			return false;
}

function confirm_add(error_msg)
{
		if(document.getElementById('unsub_emails_add').value=="")
		{
			alert(error_msg)
			return false;
		}
		else
		{
			document.getElementById('task').value="unsubscribe_list.add";
			document.adminForm.submit();

		}
}


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
</script>
<form		action="<?php echo JRoute::_('index.php?option=com_invitex&view=unsubscribe_list'); ?>"
 method="post" name="adminForm" id="adminForm" >
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> unsubscribe_list">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">

		<?php else : ?>
			<div id="j-main-container">
			<?php endif; ?>

			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_UNSUB_LIST'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_UNSUB_LIST'); ?>" />
				</div>

				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip"
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
				<?php endif; ?>
			</div>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="2%" >
							<input type="checkbox" onclick="Joomla.checkAll(this)" title="Check All" value="" name="checkall_toggle">
					</th>
					<th class="nowrap">
						<?php echo JHtml::_( 'grid.sort', 'INVITEE_ID', 'invitee_email', $listOrder, $listOrder); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php
			$k = 0;
			if($this->items)
			{
					$n=count( $this->items );
					for ($i=0; $i < $n; $i++)
					{
						$row = &$this->items[$i];
				?>
						<tr class="<?php echo "row$k"; ?>">
							<td >
					<?php echo JHtml::_('grid.id', $i, $row->invitee_email); ?>
							</td>
							<td >
									<?php echo $this->escape($row->invitee_email); ?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					}
			}
			else
			{?>
					<tr class="row0">
						<td class="center hidden-phone" colspan="3"><?php echo JText::_('NO_DATA');?></td>
					</tr>
		<?php	}

			?>
			</tbody>
		</table>
		<div class="pagination <?php if(JVERSION<3.0 ) echo "pager"; ?> ">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>
	<?php endif; ?>
		<div id="accordion1" class="accordion">
			<div class="accordion-group">
				<div class="accordion-heading">
					<a href="#batch" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
						<?php echo JText::_('COM_INVITEX_ADD_UNSUB_LIST');?>
					</a>
				</div>
				<div class="accordion-body collapse" id="batch" style="height: 0px;">
					<div class="accordion-inner">
						<fieldset class="batch form-inline">
							<legend><?php echo JText::_('COM_INVITEX_ADD_UNSUB_LIST');?></legend>
							<div class="combo control-group" id="batch-choose-action">
								<input type="text" class="input-xxlarge" name="unsub_emails_add" placeholder="<?php echo JText::_('COM_INVITEX_UNSUB_ADD_SUBPLACE'); ?>" id="unsub_emails_add" value="">

							</div>

							<button onclick="return confirm_add('<?php echo JText::_('COM_INVITEX_NO_EMAIL_ERROR')?>');" type="" class="btn btn-primary">
								<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
							</button>
						</fieldset>
					</div>
				</div>
			</div>
			<input type="hidden" name="option" value="com_invitex" />
			<input type="hidden" name="task" id="task" value="" />
			<input type="hidden" name="view" id="view" value="unsubscribe_list" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_( 'form.token' ); ?>
		</div>
	</div>
</form>
