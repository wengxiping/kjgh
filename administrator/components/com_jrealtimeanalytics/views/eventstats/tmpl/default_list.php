<?php 
/** 
 * @package JREALTIMEANALYTICS::EVENTSTATS::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage eventstats
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
// Ordering drag'n'drop management
if ($this->orders['order'] == 's.ordering') {
	$saveOrderingUrl = 'index.php?option=com_jrealtimeanalytics&task=eventstats.saveOrder&format=json&ajax=1';
	JHtml::_('sortablelist.sortable', 'adminList', 'adminForm', strtolower($this->orders['order_Dir']), $saveOrderingUrl);
	$this->document->addScript ( JUri::root ( true ) . '/administrator/components/com_jrealtimeanalytics/js/sortablelist.js', 'text/javascript', true );
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td class="left">
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_FILTER' ); ?>:</span>
					<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</div>
				<button class="btn btn-primary btn-mini" onclick="this.form.submit();"><?php echo JText::_('COM_JREALTIME_GO' ); ?></button>
				<button class="btn btn-primary btn-mini" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'COM_JREALTIME_RESET' ); ?></button>
			</td>
			<td class="right">
				<div class="input-prepend active hidden-phone">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_STATE' ); ?></span>
					<?php
						echo $this->lists['state'];
						echo $this->lists['categories'];
						echo $this->pagination->getLimitBox();
					?>
				</div>
			</td>
		</tr>
	</table>

	<table id="adminList" class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				<?php echo JText::_('COM_JREALTIME_NUM' ); ?>
			</th>
			<th style="width:1%">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_NAME', 's.name', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_EVENT_TYPE', 's.type', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_CAT', 'cat.title', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JText::_('COM_JREALTIME_DESCRIPTION'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_NUM_OCCURRENCES', 'event_occurrences', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
			</th>
			<th class="title hidden-phone">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_GOAL_EXPECTATION', 's.hasgoal', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
			</th>
			<th class="order hidden-phone">
				<?php echo JHtml::_('grid.sort',   'COM_JREALTIME_ORDER', 's.ordering', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display'); ?>
				<?php 
					if(isset($this->orders['order']) && $this->orders['order'] == 's.ordering'):
						echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'eventstats.saveOrder'); 
					endif;
				 ?>
			</th>
			<th style="width:5%">
				<?php echo JHtml::_('grid.sort',   'COM_JREALTIME_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display' ); ?>
			</th>
			<th style="width:5%">
				<?php echo JHtml::_('grid.sort',   'COM_JREALTIME_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'eventstats.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_jrealtimeanalytics&task=eventstats.editEntity&cid[]='. $row->id ;
		$labelOccurencesClass = $row->event_occurrences ? 'info' : 'important';
		// Access check.
		if($this->user->authorise('core.edit.state', 'com_jrealtimeanalytics')) {
			$taskPublishing	= !$row->published ? 'eventstats.publish' : 'eventstats.unpublish';
			$altPublishing 	= !$row->published ? JText::_( 'Publish' ) : JText::_( 'Unpublish' );
			$published = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? '<img alt="' . $altPublishing . '" src="' . JUri::base(true) . '/components/com_jrealtimeanalytics/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
			$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
			$published = $row->published ? '<img alt="' . $altPublishing . '" src="' . JUri::base(true) . '/components/com_jrealtimeanalytics/images/icon-16-tick.png" width="16" height="16" border="0" alt="unpublish" />' : JHtml::image('admin/publish_x.png', 'publish', '', true);
		}
		
		// Access check.
		$checked = null;
		if($this->user->authorise('core.edit', 'com_jrealtimeanalytics')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ?
						JHtml::_('jgrid.checkedout', $i, JFactory::getUser($row->checked_out)->name, $row->checked_out_time, 'eventstats.', $canCheckin) . '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>' :
						JHtml::_('grid.id', $i, $row->id);
		} else {
			$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr sortable-group-id="<?php echo $row->catid; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				// Access check.
				if ( ($row->checked_out && ( $row->checked_out != $this->user->get ('id'))) || !$this->user->authorise('core.edit', 'com_jrealtimeanalytics') ) {
					echo $row->name;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_JREALTIME_EDIT_EVENT' ); ?>">
						<?php echo $row->name; ?></a>
					<?php
				}
				?>
			</td>
			<td>
				<span class="label label-warning"><?php echo JText::_('COM_JREALTIME_' . $row->type); ?></span>
			</td>
			<td>
				<?php echo $row->cat_title ? $row->cat_title : '-'; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $row->description; ?>
			</td>
			<td class="hidden-phone">
				<span class="label label-<?php echo $labelOccurencesClass;?>"><?php echo $row->event_occurrences; ?></span>
			</td>
			<td class="hidden-phone">
				<?php if($row->hasgoal && $row->goal_expectation): ?>
					<?php if($row->event_occurrences >= $row->goal_expectation):?>
						<?php $className = 'success large'; $translation = 'COM_JREALTIME_GOAL_REACHED'; ?>
					<?php else:?>
						<?php $className = 'info'; $translation = 'COM_JREALTIME_GOAL_INPROGRESS'; ?>
					<?php endif;?>
					<span class="label label-<?php echo $className;?> hasTooltip" title="<?php echo JText::_($translation); ?>">
						<?php echo $row->event_occurrences; ?>/<?php echo $row->goal_expectation; ?>
					</span>
				<?php else: ?>
					<span class="label label-primary"><?php echo JText::_('COM_JREALTIME_NOGOAL'); ?></span>
				<?php endif; ?>
			</td>
			<td class="order hidden-phone">
				<?php 
				$ordering = $this->orders['order'] == 's.ordering'; 
				$disabled = $ordering ?  '' : 'disabled="disabled"';
				
				$iconClass = '';
				if (!$this->user->authorise('core.edit', 'com_jrealtimeanalytics')) {
					$iconClass = ' inactive';
				}
				elseif (!$ordering) {
					$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
				}
				?>
				<div style="display:inline-block" class="sortable-handler<?php echo $iconClass ?>" >
					<span class="icon-menu"></span>
				</div>
				
				<span class="moveup"><?php echo $this->pagination->orderUpIcon( $i, ($row->catid == @$this->items[$i-1]->catid), 'eventstats.moveorder_up', 'COM_JREALTIME_MOVE_UP', $ordering); ?></span>
				<span class="movedown"><?php echo $this->pagination->orderDownIcon( $i, $n, ($row->catid == @$this->items[$i+1]->catid), 'eventstats.moveorder_down', 'COM_JREALTIME_MOVE_DOWN', $ordering); ?></span>
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>"  <?php echo $disabled; ?>  class="ordering_input" style="text-align: center" />
			</td>
					
			<td>
				<?php echo $published;?>
			</td>
			<td>
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
	<tfoot>
		<td colspan="100%">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="section" value="view" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="eventstats.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>