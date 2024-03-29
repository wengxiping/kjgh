<?php 
/** 
 * @package JREALTIMEANALYTICS::HEATMAP::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage heatmap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
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
		</tr>
		<tr>
			<td class="left">
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_FROM' ); ?>:</span>
					<input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['start'];?>" class="text_area"/>
				</div>
				
				<div class="input-prepend active">
					<span class="add-on"><span class="icon-calendar"></span> <?php echo JText::_('COM_JREALTIME_FILTER_BY_DATE_TO' ); ?>:</span>
					<input type="text" name="toperiod" id="toPeriod" data-role="calendar" autocomplete="off" value="<?php echo $this->dates['to'];?>" class="text_area"/>
				</div>
				<button class="btn btn-primary btn-mini" onclick="this.form.submit();"><?php echo JText::_('COM_JREALTIME_GO' ); ?></button>
			</td>
			<td class="right">
				<div class="input-prepend active hidden-phone">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_STATE' ); ?></span>
					<?php
						echo $this->pagination->getLimitBox();
					?>
				</div>
				<div class="input-prepend active hidden-phone">
					<span class="add-on"><span class="icon-filter"></span> <?php echo JText::_('COM_JREALTIME_GRAPH_THEME' ); ?>:</span>
					<?php echo $this->lists['graphTheme'];?> 
				</div>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th style="width:1%">
				#<?php echo JText::_('COM_JREALTIME_NUM' ); ?>
			</th>
			<th style="width:1%">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
			</th>
			<th class="title">
				<?php echo JHtml::_('grid.sort',  'COM_JREALTIME_PAGEURL', 's.pageurl', @$this->orders['order_Dir'], @$this->orders['order'], 'heatmap.display'); ?>
			</th>
			<th style="width:15%">
				<?php echo JHtml::_('grid.sort',   'COM_JREALTIME_NUMCLICKS', 'numclicks', @$this->orders['order_Dir'], @$this->orders['order'], 'heatmap.display' ); ?>
			</th>
			<th style="width:5%">
				<?php echo JHtml::_('grid.sort',   'COM_JREALTIME_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'heatmap.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$extraparams = array('jes_heatmap'=>1, 'jes_from'=>$this->dates['start'], 'jes_to'=>$this->dates['to'], 'token'=>md5(date('Y-m-d')));
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$extraparams['jes_pageurl'] = $row->pageurl;
		$uniquePageUrlIdentifier = $row->pageurl;
		// Purify URLs from anchors
		if(strpos($row->pageurl, '#')) {
			$row->pageurl = preg_replace('/#(.)+$/i', '', $row->pageurl);
		}
		$heatmapUrl = strpos($row->pageurl, '?') ? $row->pageurl . '&' . http_build_query($extraparams) : $row->pageurl . '?' . http_build_query($extraparams);
		?>
		<tr>
			<td>
				#<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<?php echo JHtml::_('grid.id', $i, $row->id); ?>
			</td>
			<td>
				<a title="<?php echo JText::_('COM_JREALTME_CLICK_OPEN_HEATMAP');?>" class="hasTooltip" data-role="heatmap" href="<?php echo $heatmapUrl; ?>"><?php echo $uniquePageUrlIdentifier; ?>
					<span class="icon-out"></span>
				</a>
			</td>
			<td>
				<span class="label label-info"><?php echo $row->numclicks; ?></span>
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

	<div>
		<img src="<?php echo JUri::root();?>administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_heatmap.png' . $this->nocache;?>" />
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="heatmap.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>