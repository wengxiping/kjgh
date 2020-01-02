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
<form		action="<?php echo JRoute::_('index.php?option=com_invitex&view=reminder'); ?>"
 method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invites">
	<?php
	if (!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">

		<?php
	else : ?>
			<div id="j-main-container">
		<?php
	endif; ?>
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_REMINDER'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_REMINDER'); ?>" />
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

				<?php
				if (JVERSION >= '3.0') : ?>
					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible">
							<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
						</label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>

					<!--
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
					-->

				<?php endif; ?>

				<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php

					if (!empty($this->inviters))
					{
						$options[] = JHtml::_('select.option', 0, JText::_( 'SELECT_USER' ));

						if (count($this->inviters)>1)
						{
							$filter_inviter = $this->state->get('filter.inviter');
							$default = !empty($filter_inviter) ? $filter_inviter : 0;
							foreach($this->inviters as $key=>$value)
							{

								$options[] = JHtml::_('select.option', $key,$value['name']);
							}

							echo $this->dropdown = JHtml::_('select.genericlist', $options, 'filter_inviter', 'class="input-medium" size="1" onchange="document.adminForm.submit();" ', 'value', 'text', $default);
						}
					}

					?>
				</div>

				<div class="btn-group pull-right hidden-phone hidden-tablet">
					<?php

					if (!empty($this->providers))
					{
						$options=array();
						$options[] = JHtml::_('select.option', 0, JText::_( 'SELECT_PROVIDER' ));

						if (count($this->providers)>1)
						{
							$provider_email = $this->state->get('filter.provider_email');
							$default = !empty($provider_email) ? $provider_email : 0;

							foreach($this->providers as $key=>$value)
							{
								$provider_email = "";
								$provider_email = strtolower($value['provider_email']);
								$provider_email=str_replace("plug_techjoomlaapi_","",$provider_email);
								$provider_email=str_replace("send_","",$provider_email);
								$provider_email=ucwords($provider_email);
								$options[] = JHtml::_('select.option', $key,$provider_email);
							}

							echo $this->dropdown = JHtml::_('select.genericlist', $options, 'filter_provider_email', 'class="input-medium" size="1" onchange="document.adminForm.submit();" ', 'value', 'text', $default);
						}
					}


					?>
				</div>

						<div class="invitex_left">
						<div class="innerdiv">
								<div class="innerdiv"><?php echo JText::_('FROM_DATE');?></div><div class="innerdiv"><?php	echo JHTML::_('calendar',$this->state->get('filter.fromdate'), "fromdate" , "fromdate", '%Y-%m-%d');?></div>
						</div>
						<div class="innerdiv">
								<div class="innerdiv"> <?php echo JText::_('TO_DATE');?></div><div class="innerdiv"> <?php	echo JHTML::_('calendar',$this->state->get('filter.todate'), "todate" , "todate", '%Y-%m-%d');?></div>
						</div>
						<input type="button" class="btn  btn-small btn-primary" value="Go" onclick="document.adminForm.submit();">
					</div>
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
					<th width="1%" >
							<input type="checkbox" onclick="Joomla.checkAll(this)" title="Check All" value="" name="checkall_toggle">
					</th>
					<th  >
						<?php echo JHTML::_( 'grid.sort',  'INVITEE_ID' , 'e.invitee_email', $listDirn , $listOrder); ?>
					</th>
					<th width="20%" >
						<?php echo JHTML::_( 'grid.sort', 'LAST_INV_SENT_ON', 'e.modified', $listDirn , $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			if ($this->items)
			{
					$n=count( $this->items );
					for ($i=0; $i < $n; $i++)
					{
						$row = &$this->items[$i];
				?>
						<tr class="<?php echo "row$k"; ?>">
							<td >
								<input id="cb_<?php echo $this->escape($row->id); ?>" type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $this->escape($row->id); ?>" name="remind_emails[]" class="checked_data">
							</td>
							<td >
									<?php echo $this->escape($row->invitee_email); ?>
							</td>
							<td >
								<?php
								if ($row->modified!=0)
									echo JHTML::Date($this->escape($row->modified), JText::_( 'COM_INVITEX_DATE_FORMAT_TO_SHOW' ));
								else
									echo JHTML::Date($this->escape($row->sent_at), JText::_( 'COM_INVITEX_DATE_FORMAT_TO_SHOW' ));
 ?>
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
