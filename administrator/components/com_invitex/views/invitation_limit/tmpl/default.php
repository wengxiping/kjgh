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

	function confirm_update()
	{
			var count =		document.getElementsByName('cid[]').length;

			for(var i=0; i<count; i++)
			{
					var userid=document.getElementsByName('cid[]')[i].value;
					if(jQuery('#limit_'+userid).val()!=0 && parseInt(jQuery('#invsent_'+userid).text()) > jQuery('#limit_'+userid).val() )
					{
						alert('Invitation limit should not be less than total Invitations sent!');
						jQuery('#limit_'+userid).closest('.control-group').addClass('error');
						return false;
					}
			}

			var r = confirm("Do you really want to proceed with update??");

			if(r)
			{
				document.getElementById('task').value="invitation_limit.update_limit";
				document.adminForm.submit();
			}
			else
				return false;

	}

	function batch_process()
	{
			var count =		document.getElementsByName('cid[]').length;
			var flag=0;
			for(var i=0; i<count; i++)
			{
					if(document.adminForm["cid[]"][i].checked){
					flag ++;
					}
			}
			if(flag==0)
			{
				alert("No user selected");
				return false;
			}
			if(document.getElementById('batch_inv_limit').value=='')
			{
				alert("No limit defined");
				return false;
			}
			else
			{
				document.getElementById('task').value="invitation_limit.batch_process";
				document.adminForm.submit();
			}
	}
</script>
<form		action="<?php echo JRoute::_('index.php?option=com_invitex&view=invitation_limit'); ?>"
 method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="<?php echo INVITEX_WRAPPER_CLASS;?> invites">
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
					placeholder="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITATION_LIMIT'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_INVITEX_FILTER_SEARCH_DESC_INVITATION_LIMIT'); ?>" />
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

			<?php if (empty($this->items)) { ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_INVITEX_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php
				if(!$this->limit_installed)
					{
					?>
				<div  style="margin-bottom: 20px;">
					<div class="alert">
						<?php echo JText::_('INSTALL_NOT_COMPLETE');
						$limit_populate_link=JRoute::_(JURI::base().'index.php?option=com_invitex&tmpl=component&view=invitation_limit&layout=populateUsers');
						?>
					</div>
					<div>
						<a target='_blank' href="<?php echo $limit_populate_link;?>"><input class="btn btn-primary" type="button" value="<?php echo JText::_('Complete Invitation Limits Installation');?>" />
						</a>
					</div>
				</div>
				<?php
				}
			}
			else { ?>
				<table class="table table-striped">
			<thead>
				<tr>
					<th width="2%">
							<input type="checkbox" onclick="Joomla.checkAll(this)" title="Check All" value="" name="checkall_toggle">
					</th>
					<th class="nowrap center" width="5%" >
						<?php echo JHtml::_( 'grid.sort',  'USER_ID', 'il.userid', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap"  >
						<?php echo JHtml::_( 'grid.sort', 'USER_NAME', 'u.username', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="5%" >
						<?php echo JText::_( 'INV_SENT' ); ?>
					</th>
					<th class="nowrap" width="5%" >
						<?php echo JHtml::_( 'grid.sort', 'INV_LIMIT', 'il.limit', $listDirn, $listOrder); ?>
					</th>

				</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;

					for ($i=0, $n=count( $this->items ); $i < $n; $i++)
					{
						$row = &$this->items[$i];

						$table   = JUser::getTable();
						if($table->load( $row->userid  ))
						{
							$user=JFactory::getuser($row->userid);
						if($user){
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td >
								<input id="cb_<?php echo $this->escape($row->userid); ?>" type="checkbox" onclick="Joomla.isChecked(this.checked);" value="<?php echo $this->escape($row->userid); ?>" name="cid[]">
							</td>
							<td class="nowrap center">
									<?php echo $this->escape($row->userid); ?>
							</td>
							<td >
							<?php
								$user_name='';
								if($this->escape($row->userid))
								{
									$user_name= $row->username;
								}
								echo (!$user_name) ?JText::_('NO_USER'): $user_name;
							?>
							</td>
							<td  class="center" id="invsent_<?php echo $this->escape($row->userid); ?>">
								<?php
									echo ($row->invitations_sent)? $row->invitations_sent : '0';
								 ?>
							</td>
							<td>
								<div class="control-group">
									<div class="controls">
										<input type="text" style="text-align:right" class="input-mini" value="<?php echo $this->escape($row->limit); ?>" name="inv_limit[<?php echo $row->userid; ?>]" id="limit_<?php echo $row->userid; ?>"/>
									</div>
								</div>
							</td>

						</tr>
						<?php
						$k = 1 - $k;
						}
					}

				}
			?>
			</tbody>
		</table>
		<div class="pagination <?php if(JVERSION<3.0 ) echo "pager"; ?> ">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
		<?php
		}
		// For Batch processing load different layout
		include(JPATH_COMPONENT_ADMINISTRATOR.'/views/invitation_limit/tmpl/batch.php');
	?>

			<input type="hidden" name="option" value="com_invitex" />
			<input type="hidden" name="task"   id="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />

			<?php echo JHtml::_( 'form.token' ); ?>
		</div>
	</div>
</form>
