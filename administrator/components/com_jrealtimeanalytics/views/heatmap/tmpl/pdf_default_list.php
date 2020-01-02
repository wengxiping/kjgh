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
<style>
	table.newpage {
		width: 100%;		
	}
	div.statslabel {
		margin-bottom: 10px;
	}
	.label {
		display: inline-block;
		padding: 4px 4px;
		font-size: 15px;
		font-weight: bold;
		color: #fff;
		white-space: nowrap;
		text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
		background-color: #3a87ad;
		height: 20px;
		width: 200px;
	}
	.badge {
		background-color: #FFF;
		color: #3a87ad !important;
		padding: 15px;
	}
</style>

<br/><br/>
<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/images/icon-48-statspdf.png"/>&nbsp;&nbsp;&nbsp;&nbsp;<b style="font-size:18px;"><?php echo JText::_('COM_JREALTIME_SERVERSTATS_HEATMAP');?></b>
<hr/>
<table class="newpage">
	<tr>
		<td width="8%">
			<b style="font-size:14px;color: #3a87ad;">#<?php echo JText::_('COM_JREALTIME_NUM');?></b>
		</td>
		<td width="75%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_PAGEURL');?></b><br/>
		</td>
		<td width="10%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_NUMCLICKS');?></b>
		</td>
		<td width="7%">
			<b style="font-size:14px;color: #3a87ad;"><?php echo JText::_('COM_JREALTIME_ID');?></b>
		</td>
	</tr>

<?php
$k = 0;
$extraparams = array('jes_heatmap'=>1, 'jes_from'=>$this->dates['start'], 'jes_to'=>$this->dates['to'], 'token'=>md5(date('Y-m-d')));
for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
	$row = $this->items[$i];
	$extraparams['jes_pageurl'] = $row->pageurl;
	$heatmapUrl = strpos($row->pageurl, '?') ? $row->pageurl . '&' . http_build_query($extraparams) : $row->pageurl . '?' . http_build_query($extraparams);
	?>
	<tr>
		<td>
			#<?php echo $this->pagination->getRowOffset($i); ?>
		</td>
		<td>
			<a href="<?php echo $heatmapUrl; ?>"><?php echo $row->pageurl; ?></a>
		</td>
		<td>
			<span class="label label-info"> <?php echo $row->numclicks; ?> </span>
		</td>
		<td>
			<?php echo $row->id; ?>
		</td>
	</tr>
	<?php
}
?>
</table>

<div>
	<img src="<?php echo JPATH_ROOT;?>/administrator/components/com_jrealtimeanalytics/cache/<?php echo $this->userid . '_serverstats_heatmap.png';?>" />
</div>
	
