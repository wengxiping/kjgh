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
<form	action="<?php echo JRoute::_('index.php?option=com_invitex&view=topinviters'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invites">
	<?php if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">

		<?php else : ?>
			<div id="j-main-container">
			<?php endif; ?>

			<div id="filter-bar" class="row-fluid btn-toolbar">

				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITER'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITER'); ?>" />
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
			<div id="filter-bar" class="row-fluid btn-toolbar">
				<div class="invitex_right">
					<div class="invInnerFilter">
							<div class=""><?php echo JText::_('FROM_DATE');?></div><div class=""><?php	echo JHTML::_('calendar',$this->state->get('filter.fromdate'), "fromdate" , "fromdate", '%Y-%m-%d');?></div>
					</div>
					<div class="invInnerFilter">
							<div class=""> <?php echo JText::_('TO_DATE');?></div><div class=""> <?php	echo JHTML::_('calendar',$this->state->get('filter.todate'), "todate" , "todate", '%Y-%m-%d');?></div>
					</div>
					<div class="input-append">
						<input type="button" class="btn  btn-small btn-primary" value="Go" onclick="document.adminForm.submit();">
					</div>
				</div>
			</div>
			<div class="clearfix"> </div>

			<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::sprintf('COM_INVITEX_NO_RESULTS_FOR_INVITERS_BETWEEN_DATES',$this->state->get('filter.fromdate'), $this->state->get('filter.todate')); ?>
				</div>
			<?php
			else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>
							<?php echo JHtml::_( 'grid.sort', 'COM_INVITEX_INVITER', 'u.username', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap center" width="15%" >
							 <?php echo JHtml::_( 'grid.sort', 'TOTAL_SENT', 'total_sent', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap center" width="15%" >
							<?php echo JText::_( 'ACCEPTED' ); ?>
						</th>
						<th class="nowrap center" width="15%" >
							<?php echo JText::_( 'CLICKED' ); ?>
						</th>
						<th class="nowrap center" width="15%" >
							<?php echo JText::_( 'COM_INVITEX_USER_REGISTERED' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$k = 0;
				for ($i=0, $n=count( $this->items ); $i < $n; $i++)
				{
					$row = $this->items[$i];

					$link 	= JRoute::_( 'index.php?option=com_invitex&view=user' );

					$checked 	= JHTML::_('grid.id', $i , $row->id );


					$row->cat_link 	= JRoute::_( 'index.php?option=com_invitex&view=user' );
					?>
					<tr class="<?php echo "row$k"; ?>">

						<td>
								<?php echo $this->escape($row->username); ?>
						</td>
						<td class="nowrap center">
								<?php echo $this->escape($row->total_sent); ?>
						</td>
						<td class="nowrap center">
							<?php echo $this->escape($row->acc); ?>
						</td>
						<td class="nowrap center">
								<?php echo $this->escape($row->click); ?>
						</td>
						<td class="nowrap center">
								<?php echo $this->escape($row->registered); ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
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
