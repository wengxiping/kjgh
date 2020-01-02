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
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	techjoomla.jQuery(document).ready(function() {
		techjoomla.jQuery("#limit").removeClass('inputbox');
		techjoomla.jQuery("#limit").removeClass('input-mini');
		techjoomla.jQuery("#limit").removeAttr('size');
	});
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
<div class="<?php echo INVITEX_WRAPPER_CLASS;?>">
	<div class="invitex_title">
		<h2><?php echo JText::_('URL_STATS')?></h2>
	</div>
	<div>
		<div class="invitex_skip text-right">
			<button class="btn btn-default" onclick='window.location="<?php echo $onload_redirect?>"'><?php echo JText::_('BACK_TO_INVITEX');?></button>
		</div>
		<br><br>
	</div>
	<form action='' method=post name="adminForm" id="adminForm">
		<div class="clearfix">&nbsp;</div>
		<div id="filter-bar" class="">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip form-control"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_STATS'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn btn-default hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="glyphicon glyphicon-search"></i>
				</button>
				<button type="button" class="btn btn-default hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();">
				<i class="glyphicon glyphicon-remove"></i>
				</button>
			</div>
			<?php if (JVERSION >= '3.0') : ?>
			<div class="btn-group pull-right hidden-xs invitex-margin-left-5px">
				<label for="limit" class="element-invisible">
				<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-xs hidden-sm invitex-margin-left-5px">
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
			<div class="btn-group pull-right hidden-xs hidden-sm invitex-margin-left-5px">
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
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php
			else : ?>
		<div class="clearfix">&nbsp;</div>
		<table class="table-striped table-condensed table-bordered table-responsive inv_table" width="100%">
			<thead>
				<tr>
					<th class="text-left wordsbreak"><?php echo JHTML::_( 'grid.sort',  'NAMES' , 'u.name', $listDirn, $listOrder); ?></td>
					<th class="text-left wordsbreak"><?php echo JHTML::_( 'grid.sort', 'EMAILS', 'u.email', $listDirn, $listOrder); ?></td>
				</tr>
			</thead>
			<?php
				if($this->items)
				{
					foreach ( $this->items as $row )
					{
				?>
			<tr>
				<td class="wordsbreak"><?php echo $row->name;?></td>
				<td class="wordsbreak"><?php echo $row->email;?></td>
			</tr>
			<?php
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
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option"  value="com_invitex" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" id="view" value="urlstats" />
		<input type="hidden" id="filter_order" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" id="filter_order_Dir" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="boxchecked" value="" />
	</form>
</div>
<?php
	$path=$this->invhelperObj->getViewpath('invites','default_footer');
	include $path;
	?>
