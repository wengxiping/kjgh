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
</script>
<form		action="<?php echo JRoute::_('index.php?option=com_invitex&view=types'); ?>"
 method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> types">
	<?php
	if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}
	?>
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
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC'); ?>" />
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

			<div class="clearfix"> </div>

			<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
		<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%" class="hidden-phone">
						<input type="checkbox" onclick="Joomla.checkAll(this)" title="Check All" value="" name="checkall-toggle">
				</th>
				<th><?php echo JHTML::_( 'grid.sort',  'TITLE' , 'name', $listDirn, $listOrder); ?></th>
				<th width="15%" class="nowrap hidden-phone"><?php echo JHTML::_( 'grid.sort', 'INTERNAL_NAME' , 'internal_name', $listDirn, $listOrder); ?></th>
				<th width="12%" class="nowrap hidden-phone center"><?php echo JText::_( 'COM_INVITEX_WIDGET' ); ?></th>
				<th class="nowrap center" width="1%"><?php echo JHTML::_( 'grid.sort', 'ID' , 'id', $listDirn, $listOrder); ?></th>
			</tr>
			</thead>

		<tbody>
		<?php
			$cnt=count($row=$this->items );
			$k=0;
			if($row)
			{
				foreach($row as $type)
				{

				$edit_link 	= JRoute::_( "index.php?option=com_invitex&view=types&task=edit&layout=type&type_id=$type->id" );
				$widget_link 	= JRoute::_( "index.php?option=com_invitex&view=types&task=edit&layout=widget&tmpl=component&type_id=$type->id" );
				JHTML::_('behavior.modal', 'a.modal');
							?>
				<tr class="<?php echo 'row'.$k; ?>">
					<td class="center hidden-phone">
						<input type="checkbox" title="Checkbox for row 1" onclick="Joomla.isChecked(this.checked);" value="<?php echo $type->id; ?>" name="cid[]" id="cb0"></td>
					<td><a href="<?php echo  $edit_link?>" ><?php echo $type->name; ?></a></td>
					<td><?php echo $type->internal_name; ?></td>
					<td><a rel="{handler: 'iframe', size: {x:700, y: 300}}" class="modal" href="<?php echo  $widget_link?>" ><input type="button" value="<?php echo JText::_('VIEW_WIDGET');?>" class="btn  btn-small btn-primary"></a></td>
					<td class="center"><?php echo $type->id; ?></td>
				</tr>
					<?php
					$k++;
					if($k==2)
						$k=0;
				}
			}
		?>
		</tbody>
	</table>

	<div class="pagination <?php if(JVERSION<3.0 ) echo "pager"; ?> ">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>

	<?php endif; ?>

			<input type="hidden" name="option" value="com_invitex" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_( 'form.token' ); ?>
		</div>
	</div>
</form>
